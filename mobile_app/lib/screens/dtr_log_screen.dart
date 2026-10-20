// SBC Internship Attendance System - Mobile App
// File: dtr_log_screen.dart

import 'package:flutter/material.dart';
import '../core/api_service.dart';
import '../core/pdf_export_service.dart';

// Main widget / service implementation
class DtrLogScreen extends StatefulWidget {
  final int studentId;
  final String studentName;

  const DtrLogScreen({
    super.key,
    required this.studentId,
    this.studentName = 'Student',
  });

  @override
  State<DtrLogScreen> createState() => DtrLogScreenState();
}

class DtrLogScreenState extends State<DtrLogScreen> {
  bool _isLoading = true;
  List<dynamic> _logs = [];

  static const Color primaryNavy = Color(0xFF0F172A);

  @override
  void initState() {
    super.initState();
    fetchDtrHistory();
  }

  Future<void> fetchDtrHistory() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
    });

    final result = await ApiService.getAttendanceHistory(widget.studentId);

    if (!mounted) return;

    if (result['status'] == 'success') {
      setState(() {
        _logs = result['data'] ?? [];
        _isLoading = false;
      });
    } else {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _buildBody(),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _logs.isEmpty
            ? null
            : () {
                PdfExportService.exportDtrPdf(
                  studentName: widget.studentName,
                  ojtId: widget.studentId.toString(),
                  logs: _logs,
                );
              },
        backgroundColor: primaryNavy,
        icon: const Icon(Icons.picture_as_pdf, color: Colors.white),
        label: const Text(
          'Export Attendance Log (PDF)',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: primaryNavy));
    }

    if (_logs.isEmpty) {
      return RefreshIndicator(
        onRefresh: fetchDtrHistory,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: const [
            SizedBox(height: 100),
            Center(
              child: Column(
                children: [
                  Icon(Icons.history, size: 60, color: Colors.grey),
                  SizedBox(height: 10),
                  Text(
                    'No attendance logs found yet.',
                    style: TextStyle(color: Colors.grey, fontSize: 16),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: fetchDtrHistory,
      child: ListView.builder(
        padding: const EdgeInsets.only(
          left: 16.0,
          right: 16.0,
          top: 16.0,
          bottom: 80.0,
        ),
        itemCount: _logs.length,
        itemBuilder: (context, index) {
          final log = _logs[index];
          return Card(
            elevation: 1,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
              side: BorderSide(color: Colors.grey.shade300),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.calendar_today,
                            size: 16,
                            color: primaryNavy,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            log['date'] ?? '',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                              color: primaryNavy,
                            ),
                          ),
                        ],
                      ),
                      _buildOverallStatusBadge(log['status']),
                    ],
                  ),
                  const Divider(height: 18, thickness: 1),

                  // Morning Shift Section
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Morning Shift:',
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                          ),
                          _buildShiftBadge(log['morning_status']),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'In: ${log['time_in_morning'] ?? '--:--'}  |  Out: ${log['time_out_morning'] ?? '--:--'}',
                        style: const TextStyle(
                          fontSize: 12.5,
                          color: Colors.black87,
                        ),
                      ),
                      if (log['morning_remarks'] != null && log['morning_remarks'].toString().trim().isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: (log['morning_status'] == 'Rejected') ? Colors.red.shade50 : Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(4),
                            border: Border.all(color: (log['morning_status'] == 'Rejected') ? Colors.red.shade200 : Colors.blue.shade200),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                (log['morning_status'] == 'Rejected') ? Icons.error_outline : Icons.info_outline,
                                size: 14,
                                color: (log['morning_status'] == 'Rejected') ? Colors.red.shade700 : Colors.blue.shade700,
                              ),
                              const SizedBox(width: 5),
                              Expanded(
                                child: Text(
                                  "Dean Remark: ${log['morning_remarks']}",
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontStyle: FontStyle.italic,
                                    color: (log['morning_status'] == 'Rejected') ? Colors.red.shade800 : Colors.blue.shade900,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 10),

                  // Afternoon Shift Section
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Afternoon Shift:',
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 13,
                            ),
                          ),
                          _buildShiftBadge(log['afternoon_status']),
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'In: ${log['time_in_afternoon'] ?? '--:--'}  |  Out: ${log['time_out_afternoon'] ?? '--:--'}',
                        style: const TextStyle(
                          fontSize: 12.5,
                          color: Colors.black87,
                        ),
                      ),
                      if (log['afternoon_remarks'] != null && log['afternoon_remarks'].toString().trim().isNotEmpty) ...[
                        const SizedBox(height: 4),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: (log['afternoon_status'] == 'Rejected') ? Colors.red.shade50 : Colors.blue.shade50,
                            borderRadius: BorderRadius.circular(4),
                            border: Border.all(color: (log['afternoon_status'] == 'Rejected') ? Colors.red.shade200 : Colors.blue.shade200),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                (log['afternoon_status'] == 'Rejected') ? Icons.error_outline : Icons.info_outline,
                                size: 14,
                                color: (log['afternoon_status'] == 'Rejected') ? Colors.red.shade700 : Colors.blue.shade700,
                              ),
                              const SizedBox(width: 5),
                              Expanded(
                                child: Text(
                                  "Dean Remark: ${log['afternoon_remarks']}",
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontStyle: FontStyle.italic,
                                    color: (log['afternoon_status'] == 'Rejected') ? Colors.red.shade800 : Colors.blue.shade900,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 10),
                  const Divider(height: 12, thickness: 0.5),

                  // Event Attendance Status Footer
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Event Verification:',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: Colors.black54,
                        ),
                      ),
                      Text(
                        (log['status'] == 'Confirmed' || log['status'] == 'Approved')
                            ? 'Verified Present'
                            : (log['status'] ?? 'Recorded'),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: (log['status'] == 'Rejected')
                              ? Colors.red.shade700
                              : const Color(0xFF166534),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildOverallStatusBadge(String? status) {
    Color bg = Colors.green.shade50;
    Color border = Colors.green.shade300;
    Color text = Colors.green.shade800;
    String label = status ?? 'Present';

    if (status == 'Rejected') {
      bg = Colors.red.shade50;
      border = Colors.red.shade300;
      text = Colors.red.shade800;
      label = 'Rejected';
    } else if (status == 'Partial') {
      bg = Colors.amber.shade50;
      border = Colors.amber.shade300;
      text = Colors.amber.shade900;
      label = 'Partial Shift';
    } else if (status == 'Confirmed') {
      bg = Colors.green.shade50;
      border = Colors.green.shade300;
      text = Colors.green.shade800;
      label = 'Confirmed';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: border),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: text,
        ),
      ),
    );
  }

  Widget _buildShiftBadge(String? shiftStatus) {
    Color bg = Colors.grey.shade100;
    Color border = Colors.grey.shade300;
    Color text = Colors.grey.shade700;
    String label = shiftStatus ?? 'Pending';

    if (shiftStatus == 'Confirmed') {
      bg = Colors.green.shade50;
      border = Colors.green.shade200;
      text = Colors.green.shade800;
      label = '✓ Confirmed';
    } else if (shiftStatus == 'Rejected') {
      bg = Colors.red.shade50;
      border = Colors.red.shade200;
      text = Colors.red.shade800;
      label = '✕ Rejected';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: border),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: text,
        ),
      ),
    );
  }
}
