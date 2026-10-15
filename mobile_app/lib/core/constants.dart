import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io' show Platform;

class AppColors {
  // CIT Color Palette: Yellow, Black, and White
  static const Color primaryNavy = Color(0xFF0F172A); // CIT Deep Obsidian Black
  static const Color primaryBlack = Color(0xFF090D16); // CIT Jet Black
  static const Color accentGold = Color(0xFFF59E0B); // CIT Golden Yellow
  static const Color accentYellow = Color(0xFFFBBF24); // CIT Bright Yellow
  static const Color backgroundLight = Color(0xFFF8FAFC); // Clean Crisp White
  static const Color cardWhite = Color(0xFFFFFFFF); // Pure White Cards
  static const Color textDark = Color(0xFF0F172A);
  static const Color textMuted = Color(0xFF64748B);
  static const Color successGreen = Color(0xFF10B981);
}

class AppConfig {
  // Default host for production server (configurable dynamically in app)
  static String _activeHost = "deanadmin.infinityfreeapp.com";
  static final List<String> _customHosts = [];

  static String get serverHost => _activeHost;

  static set serverHost(String host) {
    String cleanHost = host.trim().replaceAll(RegExp(r'/+$'), '');
    if (cleanHost.isNotEmpty) {
      _activeHost = cleanHost;
      if (!_customHosts.contains(cleanHost)) {
        _customHosts.insert(0, cleanHost);
      }
      _saveServerHost(cleanHost);
    }
  }

  static Future<void> loadServerHost() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedHost = prefs.getString('server_host');
      if (savedHost != null &&
          savedHost.trim().isNotEmpty &&
          savedHost.trim() != "192.168.1.3" &&
          savedHost.trim() != "YOUR_SERVER_IP_OR_DOMAIN") {
        _activeHost = savedHost.trim().replaceAll(RegExp(r'/+$'), '');
        if (!_customHosts.contains(_activeHost)) {
          _customHosts.insert(0, _activeHost);
        }
      } else {
        _activeHost = "deanadmin.infinityfreeapp.com";
      }
    } catch (_) {
      _activeHost = "deanadmin.infinityfreeapp.com";
    }
  }

  static Future<void> _saveServerHost(String host) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('server_host', host);
    } catch (_) {}
  }

  static String getBaseUrlForHost(String host) {
    final h = host.trim().replaceAll(RegExp(r'/+$'), '');

    // Full HTTP / HTTPS endpoint
    if (h.startsWith('http://') || h.startsWith('https://')) {
      if (h.endsWith('/backend')) {
        return h;
      }
      return "$h/backend";
    }

    if (kIsWeb) {
      return "http://localhost/SBC_Internship_Attendance_System/backend";
    }
    try {
      if (Platform.isWindows && (h == 'localhost' || h == '127.0.0.1')) {
        return "http://localhost/SBC_Internship_Attendance_System/backend";
      }
    } catch (_) {}

    // Public domain name (e.g. deanadmin.infinityfreeapp.com or custom domain)
    if (h.contains('.') && !RegExp(r'^\d+\.\d+\.\d+\.\d+$').hasMatch(h)) {
      return "http://$h/backend";
    }

    // Local IP on local XAMPP network
    return "http://$h/SBC_Internship_Attendance_System/backend";
  }

  static String get baseUrl => getBaseUrlForHost(_activeHost);

  static List<String> get candidateHosts {
    final list = <String>[];
    for (final h in _customHosts) {
      if (!list.contains(h)) list.add(h);
    }
    // Add known candidate hosts for auto-discovery
    final defaults = [
      "deanadmin.infinityfreeapp.com",
      "192.168.1.3",
      "192.168.1.100",
      "192.168.1.7",
      "10.0.0.56",
      "10.0.2.2",
      "127.0.0.1",
      "localhost",
    ];
    for (final d in defaults) {
      if (!list.contains(d)) list.add(d);
    }
    return list;
  }
}


