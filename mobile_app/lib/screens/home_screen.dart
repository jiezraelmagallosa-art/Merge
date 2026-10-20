// SBC Internship Attendance System - Mobile App
// File: home_screen.dart

import 'dart:async';
import 'dart:convert';
import 'dart:ui' as ui;
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:camera/camera.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../main.dart';
import '../services/api_service.dart';
import '../core/motivational_messages.dart';
import 'dtr_log_screen.dart';
import 'journal_screen.dart';
import 'absence_screen.dart';
import 'profile_screen.dart';

// Main widget / service implementation
class HomeScreen extends StatefulWidget {
  final String studentName;
  final int studentId;

  const HomeScreen({
    super.key,
    required this.studentName,
    required this.studentId,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with WidgetsBindingObserver {
  final GlobalKey<DtrLogScreenState> _dtrLogKey =
      GlobalKey<DtrLogScreenState>();

  int _currentIndex = 0;
  bool _isActionLoading = false;
  bool _isLoadingSummary = true;


  CameraController? _cameraController;
  bool _isCameraInitialized = false;
  bool _isCameraActive = false;
  bool _isInitializingCamera = false;
  bool _isLiveCamFlipped = false;


  int _countdownSeconds = 0;
  Timer? _countdownTimer;
  bool _isCountingDown = false;


  late Timer _timer;
  Timer? _welcomeTimer;
  bool _showWelcome = true;
  DateTime _now = DateTime.now();


  int _totalDays = 0;
  String _todayStatusLabel = "Not Recorded";
  bool _hasTimedIn = false;
  bool _hasTimedOut = false;
  Map<String, dynamic>? _todayEvent;
  String _deanName = "Assigned Dean / Officer";

  static const Color primaryNavy = Color(0xFF0F172A);
  static const Color accentGold = Color(0xFFF59E0B);
  static const Color bgLight = Color(0xFFF8FAFC);
  static const Color cardWhite = Colors.white;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadLiveCamMirrorPreference();
    _fetchDashboardData();


    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          _now = DateTime.now();
        });
      }
    });


    _welcomeTimer = Timer(const Duration(seconds: 1), () {
      if (mounted) {
        setState(() {
          _showWelcome = false;
        });
      }
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused) {
      _closeCamera();
    }
  }

  Future<void> _loadLiveCamMirrorPreference() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      if (mounted) {
        setState(() {
          _isLiveCamFlipped = prefs.getBool('live_cam_flipped') ?? false;
        });
      }
    } catch (_) {}
  }

  Future<void> _toggleLiveCamMirror() async {
    setState(() {
      _isLiveCamFlipped = !_isLiveCamFlipped;
    });
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('live_cam_flipped', _isLiveCamFlipped);
    } catch (_) {}
    if (mounted) {
      ScaffoldMessenger.of(context).clearSnackBars();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.flip_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 8),
              Text(
                _isLiveCamFlipped ? 'Camera preview unmirrored (flipped)' : 'Camera preview mirrored',
                style: const TextStyle(fontWeight: FontWeight.w600),
              ),
            ],
          ),
          backgroundColor: primaryNavy,
          duration: const Duration(seconds: 2),
        ),
      );
    }
  }

  Future<void> _openCamera() async {
    if (_isInitializingCamera || _isCameraActive) return;
    setState(() {
      _isInitializingCamera = true;
    });

    if (cameras.isNotEmpty) {
      final frontCamera = cameras.firstWhere(
        (camera) => camera.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );

      _cameraController = CameraController(
        frontCamera,
        ResolutionPreset.high,
        enableAudio: false,
        imageFormatGroup: ImageFormatGroup.jpeg,
      );

      try {
        await _cameraController!.initialize();
        try {
          await _cameraController!.lockCaptureOrientation(DeviceOrientation.portraitUp);
        } catch (_) {}
        try {
          final double minZoom = await _cameraController!.getMinZoomLevel();
          await _cameraController!.setZoomLevel(minZoom);
        } catch (_) {}

        if (mounted) {
          setState(() {
            _isCameraInitialized = true;
            _isCameraActive = true;
            _isInitializingCamera = false;
          });
        }
      } catch (e) {
        debugPrint('Error initializing camera controller: $e');
        if (mounted) {
          setState(() {
            _isInitializingCamera = false;
          });
        }
      }
    } else {
      if (mounted) {
        setState(() {
          _isInitializingCamera = false;
        });
      }
    }
  }

  Future<void> _closeCamera() async {
    _countdownTimer?.cancel();
    _countdownTimer = null;
    if (_cameraController != null) {
      try {
        await _cameraController!.dispose();
      } catch (e) {
        debugPrint('Error disposing camera controller: $e');
      }
      _cameraController = null;
    }
    if (mounted) {
      setState(() {
        _isCameraInitialized = false;
        _isCameraActive = false;
        _isInitializingCamera = false;
        _isCountingDown = false;
        _countdownSeconds = 0;
      });
    }
  }

  void _cancelCountdown() {
    _countdownTimer?.cancel();
    _countdownTimer = null;
    if (mounted) {
      setState(() {
        _isCountingDown = false;
        _countdownSeconds = 0;
        _isActionLoading = false;
      });
    }
  }

  Future<bool> _startCameraCountdown({int durationInSeconds = 3}) async {
    final Completer<bool> completer = Completer<bool>();

    setState(() {
      _isCountingDown = true;
      _countdownSeconds = durationInSeconds;
    });

    _countdownTimer?.cancel();
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted || !_isCountingDown) {
        timer.cancel();
        if (!completer.isCompleted) completer.complete(false);
        return;
      }

      if (_countdownSeconds > 1) {
        setState(() {
          _countdownSeconds--;
        });
      } else {
        setState(() {
          _countdownSeconds = 0;
          _isCountingDown = false;
        });
        timer.cancel();
        if (!completer.isCompleted) completer.complete(true);
      }
    });

    return completer.future;
  }

  Future<XFile?> _captureImage() async {
    if (_cameraController == null || !_cameraController!.value.isInitialized) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Camera is not ready yet.'),
          backgroundColor: Colors.orangeAccent,
        ),
      );
      return null;
    }

    if (_cameraController!.value.isTakingPicture) {
      return null;
    }

    try {
      try {
        await _cameraController!.lockCaptureOrientation(DeviceOrientation.portraitUp);
      } catch (_) {}
      final XFile imageFile = await _cameraController!.takePicture();
      debugPrint('Image captured at path: ${imageFile.path}');
      return imageFile;
    } catch (e) {
      debugPrint('Error capturing image: $e');
      return null;
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _timer.cancel();
    _welcomeTimer?.cancel();
    _countdownTimer?.cancel();
    _closeCamera();
    super.dispose();
  }

  Future<void> _fetchDashboardData() async {
    setState(() {
      _isLoadingSummary = true;
    });

    final result = await ApiService.getDashboardSummary(widget.studentId);

    if (!mounted) return;

    if (result['status'] == 'success' && result['data'] != null) {
      final data = result['data'];
      setState(() {
        _totalDays = data['total_days'] ?? data['total_events_attended'] ?? 0;
        _todayStatusLabel = data['today_status_label'] ?? 'Not Recorded';
        _hasTimedIn = data['has_timed_in'] ?? false;
        _hasTimedOut = data['has_timed_out'] ?? false;
        _todayEvent = (data['today_event'] != null && data['today_event']['has_event'] == true)
            ? data['today_event']
            : null;
        _deanName = data['dean_name'] ?? 'Assigned Dean / Officer';
        _isLoadingSummary = false;
      });
    } else {
      setState(() {
        _isLoadingSummary = false;
      });
    }
  }

  Future<void> _handleAttendanceAction(String actionType) async {
    final String? shift = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text(
          actionType == 'time_in'
              ? 'Select Time In Shift'
              : 'Select Time Out Shift',
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            color: primaryNavy,
            fontSize: 20,
          ),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryNavy,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.wb_sunny_rounded, color: accentGold),
                label: const Text(
                  'Morning Shift',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                onPressed: () => Navigator.pop(context, 'morning'),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryNavy,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                icon: const Icon(Icons.wb_twilight_rounded, color: accentGold),
                label: const Text(
                  'Afternoon Shift',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
                onPressed: () => Navigator.pop(context, 'afternoon'),
              ),
            ),
          ],
        ),
      ),
    );

    if (shift == null) return;



    setState(() {
      _isActionLoading = true;
    });


    if (!_isCameraActive ||
        _cameraController == null ||
        !_cameraController!.value.isInitialized) {
      await _openCamera();
    }


    await Future.delayed(const Duration(milliseconds: 300));


    final bool countdownSuccess = await _startCameraCountdown(
      durationInSeconds: 3,
    );
    if (!countdownSuccess || !mounted) {
      setState(() {
        _isActionLoading = false;
        _isCountingDown = false;
      });
      return;
    }


    final XFile? capturedPhoto = await _captureImage();
    String imageBase64 = '';

    if (capturedPhoto != null) {
      try {
        Uint8List bytes = await capturedPhoto.readAsBytes();

        // Ensure captured photo is normalized to portrait orientation and mirrored horizontally like a true selfie preview
        try {
          final ui.Codec codec = await ui.instantiateImageCodec(bytes);
          final ui.FrameInfo frameInfo = await codec.getNextFrame();
          final ui.Image decodedImage = frameInfo.image;

          final recorder = ui.PictureRecorder();
          final canvas = Canvas(recorder);
          int newWidth;
          int newHeight;

          if (decodedImage.width > decodedImage.height) {
            newWidth = decodedImage.height;
            newHeight = decodedImage.width;

            // Rotate 90 degrees clockwise to portrait AND mirror horizontally
            canvas.scale(-1.0, 1.0);
            canvas.rotate(math.pi / 2);
            canvas.drawImage(decodedImage, Offset.zero, Paint());
          } else {
            newWidth = decodedImage.width;
            newHeight = decodedImage.height;

            // Mirror horizontally (flipX)
            canvas.translate(newWidth.toDouble(), 0);
            canvas.scale(-1.0, 1.0);
            canvas.drawImage(decodedImage, Offset.zero, Paint());
          }

          final ui.Image rotatedUiImage = await recorder.endRecording().toImage(newWidth, newHeight);
          final ByteData? byteData = await rotatedUiImage.toByteData(format: ui.ImageByteFormat.png);
          if (byteData != null) {
            bytes = byteData.buffer.asUint8List();
          }
        } catch (rotErr) {
          debugPrint("Orientation & mirroring normalization error: $rotErr");
        }

        imageBase64 = base64Encode(bytes);
        debugPrint(
          "Photo encoded to base64 successfully, length: ${imageBase64.length}",
        );
      } catch (e) {
        debugPrint("Error reading photo bytes: $e");
      }
    }


    await _closeCamera();

    final String fullAction = '${actionType}_$shift';
    final result = await ApiService.recordAttendance(
      studentId: widget.studentId.toString(),
      action: fullAction,
      selfiePath: capturedPhoto?.path ?? '',
      imageBase64: imageBase64,
    );

    setState(() {
      _isActionLoading = false;
    });

    if (!mounted) return;

    if (result['status'] == 'success') {
      MotivationalMessages.showMotivationalDialog(
        context,
        actionType: actionType,
        message: result['motivational_message'] ?? result['message'] ?? '',
        recordedTime: result['recorded_time'] ?? DateFormat('hh:mm a').format(DateTime.now()),
      );
      _fetchDashboardData();
      _dtrLogKey.currentState?.fetchDtrHistory();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Failed to record attendance.'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> pages = [
      _buildAttendanceHome(),
      DtrLogScreen(
        key: _dtrLogKey,
        studentId: widget.studentId,
        studentName: widget.studentName,
      ),
      JournalScreen(
        studentId: widget.studentId,
        studentName: widget.studentName,
      ),
      AbsenceScreen(studentId: widget.studentId),
      ProfileScreen(
        studentId: widget.studentId,
        studentName: widget.studentName,
      ),
    ];

    return Scaffold(
      backgroundColor: bgLight,
      appBar: AppBar(
        backgroundColor: primaryNavy,
        elevation: 0,
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: CircleAvatar(
            backgroundColor: Colors.white,
            child: ClipOval(
              child: Image.asset(
                'assets/images/cit_logo.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => const Icon(
                  Icons.school_rounded,
                  size: 20,
                  color: primaryNavy,
                ),
              ),
            ),
          ),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Student Records',
              style: TextStyle(
                fontSize: _showWelcome ? 14 : 18,
                fontWeight: _showWelcome ? FontWeight.normal : FontWeight.bold,
                color: _showWelcome ? Colors.white70 : Colors.white,
              ),
            ),
            AnimatedCrossFade(
              firstChild: Text(
                'Welcome, ${widget.studentName}',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              secondChild: const SizedBox.shrink(),
              crossFadeState: _showWelcome
                  ? CrossFadeState.showFirst
                  : CrossFadeState.showSecond,
              duration: const Duration(milliseconds: 500),
            ),
          ],
        ),
      ),
      body: IndexedStack(index: _currentIndex, children: pages),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        type: BottomNavigationBarType.fixed,
        backgroundColor: cardWhite,
        selectedItemColor: accentGold,
        unselectedItemColor: Colors.grey,
        selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.qr_code_scanner),
            label: 'Attendance',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.assessment_outlined),
            label: 'Events Log',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.edit_note_rounded),
            label: 'Journal',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.assignment_late_outlined),
            label: 'Absence',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            label: 'Profile',
          ),
        ],
      ),
    );
  }

  Widget _buildAttendanceHome() {
    String formattedTime = DateFormat('hh:mm:ss a').format(_now);
    String formattedDate = DateFormat('EEEE, MMMM d, yyyy').format(_now);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [

          Container(
            padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
            decoration: BoxDecoration(
              color: primaryNavy,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                Text(
                  formattedTime,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'monospace',
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  formattedDate,
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),


          AspectRatio(
            aspectRatio: 3 / 4,
            child: Container(
              clipBehavior: Clip.hardEdge,
              decoration: BoxDecoration(
                color: Colors.black,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: primaryNavy, width: 2),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.12),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Stack(
                alignment: Alignment.center,
                fit: StackFit.expand,
                children: [
                  if (_isCameraActive &&
                      _isCameraInitialized &&
                      _cameraController != null &&
                      _cameraController!.value.isInitialized)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: SizedBox.expand(
                        child: FittedBox(
                          fit: BoxFit.cover,
                          child: SizedBox(
                            width: _cameraController!.value.previewSize!.height,
                            height: _cameraController!.value.previewSize!.width,
                            child: Transform.flip(
                              flipX: _isLiveCamFlipped,
                              child: CameraPreview(_cameraController!),
                            ),
                          ),
                        ),
                      ),
                    )
                  else if (_isInitializingCamera)
                    const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          CircularProgressIndicator(color: accentGold),
                          SizedBox(height: 12),
                          Text(
                            'Opening Camera in Portrait...',
                            style: TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                    )
                  else
                    Center(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 24.0),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(
                              Icons.camera_alt_outlined,
                              size: 52,
                              color: accentGold,
                            ),
                            const SizedBox(height: 10),
                            const Text(
                              'Camera is OFF',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Tap button below to open camera in portrait mode for photo verification',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: _openCamera,
                              icon: const Icon(
                                Icons.videocam_rounded,
                                color: primaryNavy,
                              ),
                              label: const Text(
                                'OPEN CAMERA',
                                style: TextStyle(
                                  color: primaryNavy,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: accentGold,
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 22,
                                  vertical: 12,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(20),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                  if (_isCountingDown) ...[
                    Container(color: Colors.black.withValues(alpha: 0.25)),
                    Positioned(
                      top: 12,
                      left: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: primaryNavy.withValues(alpha: 0.85),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: accentGold, width: 1.5),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.camera_alt_rounded,
                              color: accentGold,
                              size: 16,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              _countdownSeconds > 0
                                  ? 'Get ready to pose!'
                                  : 'Smile! 📸',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      right: 16,
                      child: Text(
                        _countdownSeconds > 0 ? '$_countdownSeconds' : '📸',
                        style: TextStyle(
                          color: accentGold,
                          fontSize: _countdownSeconds > 0 ? 48 : 36,
                          fontWeight: FontWeight.w900,
                          shadows: const [
                            Shadow(
                              color: Colors.black87,
                              blurRadius: 10,
                              offset: Offset(1, 2),
                            ),
                            Shadow(
                              color: Colors.black54,
                              blurRadius: 6,
                            ),
                          ],
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 12,
                      child: TextButton.icon(
                        onPressed: _cancelCountdown,
                        icon: const Icon(
                          Icons.cancel_outlined,
                          color: Colors.white,
                          size: 18,
                        ),
                        label: const Text(
                          'Cancel Timer',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        style: TextButton.styleFrom(
                          backgroundColor: Colors.red.withValues(alpha: 0.85),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 6,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                        ),
                      ),
                    ),
                  ],

                  if (_isCameraActive &&
                      _isCameraInitialized &&
                      !_isCountingDown) ...[
                    Positioned(
                      top: 12,
                      right: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: accentGold,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            CircleAvatar(radius: 4, backgroundColor: Colors.red),
                            SizedBox(width: 4),
                            Text(
                              'LIVE CAMERA',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: Colors.black,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    Positioned(
                      top: 8,
                      left: 8,
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            onPressed: _closeCamera,
                            icon: const Icon(
                              Icons.close_rounded,
                              color: Colors.white,
                            ),
                            tooltip: 'Close Camera',
                            style: IconButton.styleFrom(
                              backgroundColor: Colors.black54,
                            ),
                          ),
                          const SizedBox(width: 6),
                          IconButton(
                            onPressed: _toggleLiveCamMirror,
                            icon: const Icon(
                              Icons.flip_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                            tooltip: 'Mirror / Flip Camera',
                            style: IconButton.styleFrom(
                              backgroundColor: Colors.black54,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),


          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _isActionLoading
                      ? null
                      : () => _handleAttendanceAction('time_in'),
                  icon: const Icon(Icons.login_rounded, color: Colors.white),
                  label: const Text(
                    'TIME IN',
                    style: TextStyle(color: Colors.white),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryNavy,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _isActionLoading
                      ? null
                      : () => _handleAttendanceAction('time_out'),
                  icon: const Icon(Icons.logout_rounded, color: primaryNavy),
                  label: const Text(
                    'TIME OUT',
                    style: TextStyle(color: primaryNavy),
                  ),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    side: const BorderSide(color: primaryNavy, width: 1.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // Today's Scheduled Event Card
          _buildTodayEventCard(),
          const SizedBox(height: 14),

          // Student Attendance Standing & Summary Card
          _buildAttendanceSummaryCard(),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _buildTodayEventCard() {
    final bool hasEvent = _todayEvent != null;
    final String eventName = hasEvent ? (_todayEvent!['event_name'] ?? 'Department Event') : 'No Scheduled Event Today';
    final String loginTime = hasEvent ? (_todayEvent!['login_time'] ?? '--:--') : '--:--';
    final String logoutTime = hasEvent ? (_todayEvent!['logout_time'] ?? '--:--') : '--:--';
    final String cutoff = hasEvent ? (_todayEvent!['login_cutoff_time'] ?? '--:--') : '--:--';

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardWhite,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.event_note_rounded, color: primaryNavy, size: 20),
                  SizedBox(width: 8),
                  Text(
                    "Today's Event",
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: primaryNavy,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: hasEvent ? const Color(0xFFDCFCE7) : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  hasEvent ? 'ACTIVE EVENT' : 'NO EVENT',
                  style: TextStyle(
                    color: hasEvent ? const Color(0xFF166534) : Colors.black54,
                    fontWeight: FontWeight.bold,
                    fontSize: 10,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            eventName,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: primaryNavy,
            ),
          ),
          const SizedBox(height: 6),
          if (hasEvent) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: primaryNavy.withValues(alpha: 0.04),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Login: $loginTime', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: primaryNavy)),
                  Text('Cutoff: $cutoff', style: const TextStyle(fontSize: 12, color: Colors.black54)),
                  Text('Logout: $logoutTime', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: primaryNavy)),
                ],
              ),
            ),
          ] else ...[
            const Text(
              'No department assembly or activity is currently scheduled for today. The Dean or Officer will announce upcoming events.',
              style: TextStyle(fontSize: 12, color: Colors.black54, height: 1.3),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAttendanceSummaryCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardWhite,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.verified_user_rounded, color: primaryNavy, size: 20),
              SizedBox(width: 8),
              Text(
                'Attendance Status & Records',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: primaryNavy,
                ),
              ),
            ],
          ),
          const Divider(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Total Events Attended:',
                style: TextStyle(color: Colors.black54, fontSize: 13),
              ),
              Text(
                _isLoadingSummary ? 'Loading...' : '$_totalDays Events',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: primaryNavy,
                  fontSize: 14,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                "Today's Attendance Status:",
                style: TextStyle(color: Colors.black54, fontSize: 13),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: (_hasTimedIn || _todayStatusLabel.contains('Present') || _todayStatusLabel.contains('Verified'))
                      ? const Color(0xFFDCFCE7)
                      : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (_hasTimedIn) ...[
                      const Icon(Icons.check_circle, size: 12, color: Color(0xFF166534)),
                      const SizedBox(width: 4),
                    ],
                    Text(
                      _isLoadingSummary
                          ? 'Updating...'
                          : (_hasTimedOut
                              ? 'Completed'
                              : (_hasTimedIn ? 'Timed In (Present)' : _todayStatusLabel)),
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: (_hasTimedIn || _todayStatusLabel.contains('Present') || _todayStatusLabel.contains('Verified'))
                            ? const Color(0xFF166534)
                            : Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Supervising Officer / Dean:',
                style: TextStyle(color: Colors.black54, fontSize: 13),
              ),
              Flexible(
                child: Text(
                  _deanName,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    color: primaryNavy,
                    fontSize: 12.5,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
