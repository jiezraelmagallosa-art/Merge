// SBC Internship Attendance System - Mobile App
// File: api_service.dart

import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:encrypt/encrypt.dart' as enc;
import '../core/constants.dart';

/// Transparent HTTP Client that handles standard requests and solves
/// InfinityFree anti-bot security challenges (AES cookie challenge) automatically.
class InfinityHttpClient {
  static String? _testCookie;
  static const String _userAgent =
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

  static Uint8List _hexToBytes(String hex) {
    final result = Uint8List(hex.length ~/ 2);
    for (int i = 0; i < hex.length; i += 2) {
      result[i ~/ 2] = int.parse(hex.substring(i, i + 2), radix: 16);
    }
    return result;
  }

  static String _bytesToHex(List<int> bytes) {
    return bytes.map((b) => b.toRadixString(16).padLeft(2, '0')).join();
  }

  static String? _solveChallenge(String html) {
    try {
      final reg = RegExp(
        r'a=toNumbers\("([a-f0-9]+)"\),b=toNumbers\("([a-f0-9]+)"\),c=toNumbers\("([a-f0-9]+)"\)',
      );
      final match = reg.firstMatch(html);
      if (match == null) return null;

      final keyBytes = _hexToBytes(match.group(1)!);
      final ivBytes = _hexToBytes(match.group(2)!);
      final cipherBytes = _hexToBytes(match.group(3)!);

      final key = enc.Key(keyBytes);
      final iv = enc.IV(ivBytes);
      final encrypter = enc.Encrypter(
        enc.AES(key, mode: enc.AESMode.cbc, padding: null),
      );
      final decrypted = encrypter.decryptBytes(
        enc.Encrypted(cipherBytes),
        iv: iv,
      );
      return _bytesToHex(decrypted);
    } catch (_) {
      return null;
    }
  }

  static Map<String, String> _buildHeaders(Map<String, String>? headers) {
    final map = <String, String>{};
    if (!kIsWeb) {
      map['User-Agent'] = _userAgent;
      if (_testCookie != null && _testCookie!.isNotEmpty) {
        map['Cookie'] = '__test=$_testCookie';
      }
    }
    if (headers != null) {
      map.addAll(headers);
    }
    return map;
  }

  static Future<http.Response> get(
    Uri url, {
    Map<String, String>? headers,
    Duration timeout = const Duration(seconds: 8),
  }) async {
    final response = await http
        .get(url, headers: _buildHeaders(headers))
        .timeout(timeout);

    if (!kIsWeb && (response.body.contains('aes.js') || response.body.contains('toNumbers('))) {
      final cookie = _solveChallenge(response.body);
      if (cookie != null) {
        _testCookie = cookie;
        return await http
            .get(url, headers: _buildHeaders(headers))
            .timeout(timeout);
      }
    }
    return response;
  }

  static Future<http.Response> post(
    Uri url, {
    Map<String, String>? headers,
    Object? body,
    Encoding? encoding,
    Duration timeout = const Duration(seconds: 10),
  }) async {

    final response = await http
        .post(
          url,
          headers: _buildHeaders(headers),
          body: body,
          encoding: encoding,
        )
        .timeout(timeout);

    if (!kIsWeb && (response.body.contains('aes.js') || response.body.contains('toNumbers('))) {
      final cookie = _solveChallenge(response.body);
      if (cookie != null) {
        _testCookie = cookie;
        return await http
            .post(
              url,
              headers: _buildHeaders(headers),
              body: body,
              encoding: encoding,
            )
            .timeout(timeout);
      }
    }
    return response;
  }
}

// Main API service implementation
class ApiService {
  static const String serverUnavailableMsg =
      "Server unavailable. Working in Offline Mode.";

  static Map<String, dynamic> _handleException(dynamic e) {
    debugPrint("API Exception in _handleException: $e");
    return {
      "status": "error",
      "is_connection_error": true,
      "message": serverUnavailableMsg,
    };
  }

  static Future<bool> isServerReachable() async {
    try {
      final response = await InfinityHttpClient
          .get(Uri.parse("${AppConfig.baseUrl}/get_courses.php"))
          .timeout(const Duration(seconds: 5));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data is Map && data['status'] == 'success') return true;
      }
    } catch (_) {}

    return await _tryAutoDiscover();
  }

  static Future<bool> _tryAutoDiscover() async {
    for (final host in AppConfig.candidateHosts) {
      if (host == AppConfig.serverHost) continue;
      try {
        final candidateUrl =
            "${AppConfig.getBaseUrlForHost(host)}/get_courses.php";
        final response = await InfinityHttpClient
            .get(Uri.parse(candidateUrl))
            .timeout(const Duration(seconds: 4));
        if (response.statusCode == 200) {
          final data = jsonDecode(response.body);
          if (data is Map && data['status'] == 'success') {
            AppConfig.serverHost = host;
            return true;
          }
        }
      } catch (_) {}
    }
    return false;
  }

  static Future<Map<String, dynamic>> login(
    String email,
    String password,
  ) async {
    try {
      final response = await InfinityHttpClient
          .post(
            Uri.parse("${AppConfig.baseUrl}/login.php"),
            headers: {"Content-Type": "application/json"},
            body: jsonEncode({
              "email": email,
              "password": password,
              "user_type": "student",
            }),
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        try {
          final errBody = jsonDecode(response.body);
          if (errBody is Map<String, dynamic> &&
              errBody.containsKey('message')) {
            return errBody;
          }
        } catch (_) {}
      }
    } catch (_) {
      bool found = await _tryAutoDiscover();
      if (found) {
        try {
          final retryResponse = await InfinityHttpClient
              .post(
                Uri.parse("${AppConfig.baseUrl}/login.php"),
                headers: {"Content-Type": "application/json"},
                body: jsonEncode({
                  "email": email,
                  "password": password,
                  "user_type": "student",
                }),
              )
              .timeout(const Duration(seconds: 8));

          if (retryResponse.statusCode == 200) {
            return jsonDecode(retryResponse.body);
          } else {
            try {
              final errBody = jsonDecode(retryResponse.body);
              if (errBody is Map<String, dynamic> &&
                  errBody.containsKey('message')) {
                return errBody;
              }
            } catch (_) {}
          }
        } catch (e) {
          return _handleException(e);
        }
      }
    }
    return {
      "status": "error",
      "message": serverUnavailableMsg,
    };
  }

  static Future<Map<String, dynamic>> registerStudent({
    required String fullName,
    String studentNumber = "",
    required String idNo,
    required String email,
    required String password,
    int courseId = 1,
    int? deanId,
    int? siteId,
    String yearLevel = "4",
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/register.php");

    try {
      final response = await InfinityHttpClient
          .post(
            url,
            headers: {"Content-Type": "application/json"},
            body: jsonEncode({
              "full_name": fullName,
              "student_number": studentNumber.isNotEmpty ? studentNumber : idNo,
              "id_no": idNo,
              "email": email,
              "password": password,
              "course_id": courseId,
              "dean_id": deanId,
              "site_id": siteId ?? 1,
              "year_level": yearLevel,
            }),
          )
          .timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        try {
          final errBody = jsonDecode(response.body);
          if (errBody is Map<String, dynamic> &&
              errBody.containsKey('message')) {
            return errBody;
          }
        } catch (_) {}
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getDeans() async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_deans.php");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getCourses() async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_courses.php");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getSites() async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_sites.php");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> updateStudentSite({
    required int studentId,
    required int newSiteId,
    String? remarks,
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/update_student_site.php");

    try {
      final response = await InfinityHttpClient.post(
        url,
        headers: {"Content-Type": "application/json"},
        body: jsonEncode({
          "student_id": studentId,
          "new_site_id": newSiteId,
          "remarks": remarks ?? "Transferred to new training facility",
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        try {
          return jsonDecode(response.body);
        } catch (_) {
          return {
            "status": "error",
            "message": "Failed to update training site (HTTP ${response.statusCode})",
          };
        }
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> recordAttendance({
    required String studentId,
    required String action,
    String selfiePath = '',
    String imageBase64 = '',
    String? customTime,
    String? customDate,
    double latitude = 0.0,
    double longitude = 0.0,
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/submit_attendance.php");

    try {
      final response = await InfinityHttpClient
          .post(
            url,
            headers: {"Content-Type": "application/json"},
            body: jsonEncode({
              "ojt_id": studentId,
              "student_id": studentId,
              "shift_type": action,
              "custom_time": customTime,
              "custom_date": customDate,
              "image_base64": imageBase64,
              "latitude": latitude,
              "longitude": longitude,
            }),
          )
          .timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getDashboardSummary(dynamic studentId) async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_dashboard_summary.php?student_id=$studentId");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getAttendanceHistory(dynamic studentId) async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_attendance_history.php?student_id=$studentId");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> submitAbsenceRequest(
    int studentId,
    String dateAbsent,
    String reason, {
    String imageBase64 = '',
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/submit_absence.php");

    try {
      final response = await InfinityHttpClient
          .post(
            url,
            headers: {"Content-Type": "application/json"},
            body: jsonEncode({
              "student_id": studentId,
              "date_absent": dateAbsent,
              "reason": reason,
              "image_base64": imageBase64,
            }),
          )
          .timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          "status": "error",
          "message": serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getAbsenceHistory(dynamic studentId) async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_absence_history.php?student_id=$studentId");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getStudentProfile(dynamic studentId) async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_student_profile.php?student_id=$studentId");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 6));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> updateStudentProfile({
    required int studentId,
    String? fullName,
    String? idNo,
    int? courseId,
    int? deanId,
    int? siteId,
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/update_student_profile.php");

    try {
      final response = await InfinityHttpClient.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'student_id': studentId,
          if (fullName != null) 'full_name': fullName,
          if (idNo != null) 'id_no': idNo,
          if (courseId != null && courseId > 0) 'course_id': courseId,
          if (deanId != null && deanId > 0) 'dean_id': deanId,
          if (siteId != null && siteId > 0) 'site_id': siteId,
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> submitDailyJournal({
    required int studentId,
    required String entryDate,
    required String tasksCompleted,
    String? learningsReflection,
    String? challengesEncountered,
  }) async {
    final url = Uri.parse("${AppConfig.baseUrl}/submit_journal.php");

    try {
      final response = await InfinityHttpClient.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'student_id': studentId,
          'entry_date': entryDate,
          'tasks_completed': tasksCompleted,
          'learnings_reflection': learningsReflection ?? '',
          'challenges_encountered': challengesEncountered ?? '',
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }

  static Future<Map<String, dynamic>> getStudentJournals(dynamic studentId) async {
    final url = Uri.parse("${AppConfig.baseUrl}/get_student_journals.php?student_id=$studentId");

    try {
      final response = await InfinityHttpClient.get(url).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        return {
          'status': 'error',
          'message': serverUnavailableMsg,
        };
      }
    } catch (e) {
      return _handleException(e);
    }
  }
}
