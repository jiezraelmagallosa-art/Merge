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
  // Default host for local XAMPP server
  static String _defaultHost() {
    if (kIsWeb) return "localhost";
    try {
      // 192.168.1.8 is the PC's Wi-Fi IP address for physical phones
      if (Platform.isAndroid) return "192.168.1.8";
      if (Platform.isWindows || Platform.isLinux || Platform.isMacOS) return "localhost";
    } catch (_) {}
    return "localhost";
  }

  static String _activeHost = _defaultHost();
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
          !savedHost.toLowerCase().contains("infinityfree") &&
          !savedHost.toLowerCase().contains("sbc") &&
          savedHost.trim() != "YOUR_SERVER_IP_OR_DOMAIN" &&
          savedHost.trim() != "10.0.0.56") {
        _activeHost = savedHost.trim().replaceAll(RegExp(r'/+$'), '');
        if (!_customHosts.contains(_activeHost)) {
          _customHosts.insert(0, _activeHost);
        }
      } else {
        _activeHost = _defaultHost();
      }
    } catch (_) {
      _activeHost = _defaultHost();
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
      if (h == 'localhost' || h == '127.0.0.1' || h.isEmpty) {
        return "http://localhost/CIT%20Attendance%20System/backend";
      }
      return "http://$h/CIT%20Attendance%20System/backend";
    }
    try {
      if (Platform.isWindows && (h == 'localhost' || h == '127.0.0.1')) {
        return "http://localhost/CIT%20Attendance%20System/backend";
      }
    } catch (_) {}

    if (h == 'localhost' || h == '127.0.0.1') {
      try {
        if (Platform.isAndroid) {
          return "http://10.0.2.2/CIT%20Attendance%20System/backend";
        }
      } catch (_) {}
      return "http://localhost/CIT%20Attendance%20System/backend";
    }

    // Local IP on local XAMPP network
    return "http://$h/CIT%20Attendance%20System/backend";
  }

  static String get baseUrl => getBaseUrlForHost(_activeHost);

  static List<String> get candidateHosts {
    final list = <String>[];
    for (final h in _customHosts) {
      if (!list.contains(h)) list.add(h);
    }
    // Only local candidate hosts on XAMPP network
    final defaults = [
      "192.168.1.8",
      "localhost",
      "127.0.0.1",
      "10.0.2.2",
      "192.168.1.3",
      "192.168.1.100",
      "192.168.1.7",
    ];
    for (final d in defaults) {
      if (!list.contains(d)) list.add(d);
    }
    return list;
  }
}


