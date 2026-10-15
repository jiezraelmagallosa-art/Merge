// SBC Internship Attendance System - Mobile App
// File: attendance_screen.dart

import 'dart:convert';
import 'dart:typed_data';
import 'dart:ui' as ui;
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import '../core/constants.dart';
import '../main.dart';
import '../services/api_service.dart';
import '../core/motivational_messages.dart';
import '../models/attendance.dart';


// Main widget / service implementation
class AttendanceScreen extends StatefulWidget {
  final String studentName;
  final int studentId;

  const AttendanceScreen({
    super.key,
    required this.studentName,
    required this.studentId,
  });

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  CameraController? _cameraController;
  bool _isCameraInitialized = false;
  bool _isActionLoading = false;
  List<AttendanceModel> _recentLogs = [];

  @override
  void initState() {
    super.initState();
    _initCamera();
    _loadLogs();
  }

  Future<void> _initCamera() async {
    if (cameras.isNotEmpty) {

      CameraDescription selectedCamera = cameras.firstWhere(
        (cam) => cam.lensDirection == CameraLensDirection.front,
        orElse: () => cameras.first,
      );

      _cameraController = CameraController(
        selectedCamera,
        ResolutionPreset.medium,
        enableAudio: false,
      );

      try {
        await _cameraController!.initialize();
        if (mounted) {
          setState(() {
            _isCameraInitialized = true;
          });
        }
      } catch (e) {
        debugPrint("Camera init error: $e");
      }
    }
  }

  Future<void> _loadLogs() async {
    final result = await ApiService.getAttendanceHistory(widget.studentId);
    if (mounted && result['status'] == 'success' && result['data'] != null) {
      final List rawList = result['data'];
      setState(() {
        _recentLogs = rawList.map((e) => AttendanceModel.fromJson(e)).toList();
      });
    }
  }

  Future<void> _handleAttendanceSubmit(String action) async {
    setState(() {
      _isActionLoading = true;
    });

    String selfiePath = '';
    String imageBase64 = '';
    if (_cameraController != null && _cameraController!.value.isInitialized) {
      try {
        final XFile photo = await _cameraController!.takePicture();
        selfiePath = photo.path;
        Uint8List bytes = await photo.readAsBytes();

        // Ensure captured photo is normalized to portrait and mirrored horizontally like preview
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
            canvas.scale(-1.0, 1.0);
            canvas.rotate(math.pi / 2);
            canvas.drawImage(decodedImage, Offset.zero, Paint());
          } else {
            newWidth = decodedImage.width;
            newHeight = decodedImage.height;
            canvas.translate(newWidth.toDouble(), 0);
            canvas.scale(-1.0, 1.0);
            canvas.drawImage(decodedImage, Offset.zero, Paint());
          }

          final ui.Image processedUiImage = await recorder.endRecording().toImage(newWidth, newHeight);
          final ByteData? byteData = await processedUiImage.toByteData(format: ui.ImageByteFormat.png);
          if (byteData != null) {
            bytes = byteData.buffer.asUint8List();
          }
        } catch (normErr) {
          debugPrint("Photo normalization error: $normErr");
        }

        imageBase64 = base64Encode(bytes);
      } catch (e) {
        debugPrint("Failed to take photo: $e");
      }
    }

    final result = await ApiService.recordAttendance(
      studentId: widget.studentId.toString(),
      action: action,
      selfiePath: selfiePath,
      imageBase64: imageBase64,
    );


    setState(() {
      _isActionLoading = false;
    });

    if (!mounted) return;

    final bool isSuccess = result['status'] == 'success';
    final msg = result['motivational_message'] ?? result['message'] ?? 'Attendance Processed';

    if (isSuccess) {
      MotivationalMessages.showMotivationalDialog(
        context,
        actionType: action,
        message: msg,
        recordedTime: result['recorded_time'],
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(
                Icons.error_outline_rounded,
                color: Colors.white,
              ),
              const SizedBox(width: 8),
              Expanded(child: Text(msg)),
            ],
          ),
          backgroundColor: Colors.redAccent,
          duration: const Duration(seconds: 4),
        ),
      );
    }


    _loadLogs();
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Attendance Check-In'),
        backgroundColor: AppColors.primaryNavy,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                children: [

                  AspectRatio(
                    aspectRatio: 3 / 4,
                    child: Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.black,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.primaryNavy.withValues(alpha: 0.2)),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: _isCameraInitialized && _cameraController != null
                            ? SizedBox.expand(
                                child: FittedBox(
                                  fit: BoxFit.cover,
                                  child: SizedBox(
                                    width: _cameraController!.value.previewSize!.height,
                                    height: _cameraController!.value.previewSize!.width,
                                    child: Transform.flip(
                                      flipX: false,
                                      child: CameraPreview(_cameraController!),
                                    ),
                                  ),
                                ),
                              )
                            : const Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.camera_alt_outlined, size: 48, color: Colors.grey),
                                    SizedBox(height: 8),
                                    Text("Camera Ready (Portrait Mode)"),
                                  ],
                                ),
                              ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),


                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isActionLoading ? null : () => _handleAttendanceSubmit('time_in_morning'),
                          icon: const Icon(Icons.login_rounded, color: Colors.white),
                          label: const Text('TIME IN', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primaryNavy,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _isActionLoading ? null : () => _handleAttendanceSubmit('time_out_morning'),
                          icon: const Icon(Icons.logout_rounded, color: Colors.white),
                          label: const Text('TIME OUT', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.amber.shade800,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),


                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        "Today's Records",
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.primaryNavy),
                      ),
                      IconButton(
                        icon: const Icon(Icons.refresh),
                        onPressed: _loadLogs,
                      )
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_recentLogs.isEmpty)
                    const Text("No records captured today yet.", style: TextStyle(color: Colors.grey))
                  else
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _recentLogs.length,
                      itemBuilder: (context, index) {
                        final log = _recentLogs[index];
                        final isPending = log.syncStatus == 'Pending';

                        return Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: isPending ? Colors.orange.shade100 : Colors.green.shade100,
                              child: Icon(
                                isPending ? Icons.cloud_upload_outlined : Icons.check_circle_outline,
                                color: isPending ? Colors.orange.shade900 : Colors.green.shade900,
                              ),
                            ),
                            title: Text("Date: ${log.date} - ${log.timeIn}"),
                            subtitle: Text("Student ID: ${log.studentId}"),
                            trailing: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: isPending ? Colors.orange.shade100 : Colors.green.shade100,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                isPending ? 'Pending Upload' : 'Synced',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: isPending ? Colors.orange.shade900 : Colors.green.shade900,
                                ),
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                ],
              ),
            ),
    );
  }
}
