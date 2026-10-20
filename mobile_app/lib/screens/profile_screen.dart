// CIT Attendance System - Mobile App
// File: profile_screen.dart

import 'package:flutter/material.dart';
import '../core/api_service.dart';
import '../core/constants.dart';
import 'login_screen.dart';

// Main profile screen for CIT Event Attendance
class ProfileScreen extends StatefulWidget {
  final int studentId;
  final String studentName;

  const ProfileScreen({
    super.key,
    required this.studentId,
    required this.studentName,
  });

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  bool _isLoading = true;
  Map<String, dynamic> _profileData = {};

  static const Color primaryNavy = AppColors.primaryNavy;
  static const Color accentGold = AppColors.accentGold;

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    setState(() {
      _isLoading = true;
    });

    final result = await ApiService.getStudentProfile(widget.studentId);

    if (!mounted) return;

    if (result['status'] == 'success' && result['data'] != null) {
      setState(() {
        _profileData = result['data'];
        _isLoading = false;
      });
    } else {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _handleLogout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text(
          'Confirm Logout',
          style: TextStyle(color: primaryNavy, fontWeight: FontWeight.bold),
        ),
        content: const Text('Are you sure you want to log out of your session?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
            onPressed: () {
              Navigator.pop(context);
              Navigator.pushAndRemoveUntil(
                context,
                MaterialPageRoute(builder: (context) => const LoginScreen()),
                (route) => false,
              );
            },
            child: const Text('LOGOUT', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: AppColors.backgroundLight,
        body: Center(child: CircularProgressIndicator(color: primaryNavy)),
      );
    }

    final String fullName = _profileData['full_name'] ?? widget.studentName;
    final String studentNumber = _profileData['student_number'] ?? 'N/A';
    final String email = _profileData['email'] ?? 'N/A';
    final String courseCode = _profileData['course_code'] ?? 'BSIS';
    final String courseName = _profileData['course_name'] ?? 'Bachelor of Science in Information Systems';
    final int totalEventDays = _profileData['total_event_days'] ?? 0;
    final String attendanceStatus = _profileData['attendance_status'] ?? 'No Log Today';
    final String standing = _profileData['standing'] ?? 'Good Standing';

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      body: RefreshIndicator(
        onRefresh: _fetchProfile,
        color: primaryNavy,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // Profile Header Card
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [primaryNavy, Color(0xFF1E293B)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    children: [
                      Stack(
                        children: [
                          const CircleAvatar(
                            radius: 40,
                            backgroundColor: accentGold,
                            child: Icon(Icons.person, size: 50, color: primaryNavy),
                          ),
                          Positioned(
                            bottom: 0,
                            right: 0,
                            child: CircleAvatar(
                              radius: 14,
                              backgroundColor: Colors.white,
                              child: ClipOval(
                                child: Image.asset(
                                  'assets/images/cit_logo.png',
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        fullName,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Student No: $studentNumber',
                        style: const TextStyle(color: Colors.white70, fontSize: 13),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        email,
                        style: const TextStyle(color: Colors.white60, fontSize: 12),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 12),

              // Edit Profile & Assigned Dean Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _showEditAccountDialog,
                  icon: const Icon(Icons.manage_accounts_rounded, size: 18),
                  label: const Text(
                    'Edit Profile & Dean Assignment',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryNavy,
                    foregroundColor: Colors.white,
                    elevation: 1,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(height: 14),

              // Academic Program Card
              _buildInfoCard(
                title: 'Academic Program',
                icon: Icons.school_outlined,
                items: [
                  {'label': 'Course', 'value': '$courseCode - $courseName'},
                  {'label': 'Institutional ID', 'value': _profileData['id_no'] ?? 'N/A'},
                  {'label': 'Year Level', 'value': 'Year ${_profileData['year_level'] ?? "4"}'},
                ],
              ),
              const SizedBox(height: 12),

              // Assigned Dean / Event Officer Card
              _buildInfoCard(
                title: 'Assigned Dean / Officer',
                icon: Icons.supervisor_account_outlined,
                items: [
                  {'label': 'Dean / Officer', 'value': _profileData['dean_name'] ?? 'Unassigned'},
                  if (_profileData['dean_email'] != null && _profileData['dean_email'].toString().isNotEmpty)
                    {'label': 'Contact Email', 'value': _profileData['dean_email']},
                ],
              ),
              const SizedBox(height: 12),

              // Event Attendance Status & Standing Card (Replaces training site and hours)
              Card(
                elevation: 1,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
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
                          const Row(
                            children: [
                              Icon(Icons.event_available_rounded, color: primaryNavy),
                              SizedBox(width: 8),
                              Text(
                                'Event Attendance Standing',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: primaryNavy,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFDCFCE7),
                              borderRadius: BorderRadius.circular(6),
                              border: Border.all(color: const Color(0xFF86EFAC)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.check_circle_rounded, color: Color(0xFF166534), size: 12),
                                const SizedBox(width: 4),
                                Text(
                                  standing.toUpperCase(),
                                  style: const TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF166534),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Official CIT Department event attendance monitored by your Dean and Officers.',
                        style: TextStyle(fontSize: 11, color: Colors.black54),
                      ),
                      const Divider(height: 20),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Total Events Attended:', style: TextStyle(color: Colors.black54, fontSize: 13)),
                          Text(
                            '$totalEventDays Events',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: primaryNavy,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text("Today's Event Status:", style: TextStyle(color: Colors.black54, fontSize: 13)),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: attendanceStatus.contains('Present')
                                  ? const Color(0xFFDCFCE7)
                                  : Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              attendanceStatus,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: attendanceStatus.contains('Present')
                                    ? const Color(0xFF166534)
                                    : Colors.black54,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Verification Method:', style: TextStyle(color: Colors.black54, fontSize: 13)),
                          const Text(
                            'Live Photo & Verification',
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              color: primaryNavy,
                              fontSize: 12,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 24),

              // Logout Session Button
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _handleLogout,
                  icon: const Icon(Icons.logout, color: Colors.redAccent),
                  label: const Text(
                    'LOGOUT SESSION',
                    style: TextStyle(
                      color: Colors.redAccent,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: Colors.redAccent, width: 1.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard({
    required String title,
    required IconData icon,
    required List<Map<String, String>> items,
  }) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(color: Colors.grey.shade300),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: primaryNavy),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: primaryNavy,
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            ...items.map((item) => Padding(
                  padding: const EdgeInsets.only(bottom: 8.0),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(
                        width: 120,
                        child: Text(
                          item['label']!,
                          style: const TextStyle(
                            color: Colors.black54,
                            fontSize: 13,
                          ),
                        ),
                      ),
                      Expanded(
                        child: Text(
                          item['value']!,
                          style: const TextStyle(
                            fontWeight: FontWeight.w600,
                            color: AppColors.textDark,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
                  ),
                )),
          ],
        ),
      ),
    );
  }

  Future<void> _showEditAccountDialog() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(
        child: CircularProgressIndicator(color: primaryNavy),
      ),
    );

    final results = await Future.wait([
      ApiService.getDeans(),
      ApiService.getCourses(),
    ]);

    if (!mounted) return;
    Navigator.pop(context);

    final deansRes = results[0];
    final coursesRes = results[1];

    final List<dynamic> deans = (deansRes['status'] == 'success') ? (deansRes['data'] ?? []) : [];
    final List<dynamic> courses = (coursesRes['status'] == 'success') ? (coursesRes['data'] ?? []) : [];

    final nameCtrl = TextEditingController(text: _profileData['full_name'] ?? widget.studentName);
    final idNoCtrl = TextEditingController(text: _profileData['id_no'] ?? '');

    int? selectedDeanId = int.tryParse(_profileData['dean_id']?.toString() ?? '');
    int? selectedCourseId = int.tryParse(_profileData['course_id']?.toString() ?? '');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (bCtx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.edit_note_rounded, color: primaryNavy, size: 24),
                            SizedBox(width: 8),
                            Text(
                              'Edit Profile & Assigned Dean',
                              style: TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                                color: primaryNavy,
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0F7FF),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFBAE6FD)),
                      ),
                      child: const Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.info_outline, size: 16, color: primaryNavy),
                          SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Assigning your Dean or Department Officer automatically links your attendance records to their event attendance roster.',
                              style: TextStyle(fontSize: 11.5, color: primaryNavy, height: 1.3),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Full Name
                    const Text('Full Name', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
                    const SizedBox(height: 4),
                    TextFormField(
                      controller: nameCtrl,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Institutional ID
                    const Text('Institutional ID (Optional)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
                    const SizedBox(height: 4),
                    TextFormField(
                      controller: idNoCtrl,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Assigned Dean Dropdown
                    const Text('Assigned Dean / Officer', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
                    const SizedBox(height: 4),
                    DropdownButtonFormField<int>(
                      value: deans.any((d) => int.tryParse(d['user_id'].toString()) == selectedDeanId) ? selectedDeanId : null,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      hint: const Text('Select Assigned Dean', style: TextStyle(fontSize: 12)),
                      items: deans.map<DropdownMenuItem<int>>((d) {
                        final dId = int.tryParse(d['user_id'].toString()) ?? 0;
                        return DropdownMenuItem<int>(
                          value: dId,
                          child: Text(
                            d['full_name'] ?? 'Dean',
                            style: const TextStyle(fontSize: 12.5),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          selectedDeanId = val;
                        });
                      },
                    ),
                    const SizedBox(height: 12),

                    // Academic Program Dropdown
                    const Text('Academic Program', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
                    const SizedBox(height: 4),
                    DropdownButtonFormField<int>(
                      value: courses.any((c) => int.tryParse(c['course_id'].toString()) == selectedCourseId) ? selectedCourseId : null,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      hint: const Text('Select Academic Course', style: TextStyle(fontSize: 12)),
                      items: courses.map<DropdownMenuItem<int>>((c) {
                        final cId = int.tryParse(c['course_id'].toString()) ?? 0;
                        return DropdownMenuItem<int>(
                          value: cId,
                          child: Text(
                            '${c['course_code']} - ${c['course_name']}',
                            style: const TextStyle(fontSize: 12),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          selectedCourseId = val;
                        });
                      },
                    ),
                    const SizedBox(height: 18),

                    SizedBox(
                      width: double.infinity,
                      height: 46,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryNavy,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        icon: const Icon(Icons.check_circle_outline, color: Colors.white, size: 18),
                        label: const Text(
                          'SAVE & APPLY ASSIGNMENTS',
                          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                        ),
                        onPressed: () async {
                          Navigator.pop(context);
                          await _handleProfileUpdate(
                            fullName: nameCtrl.text.trim(),
                            idNo: idNoCtrl.text.trim(),
                            deanId: selectedDeanId,
                            courseId: selectedCourseId,
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _handleProfileUpdate({
    String? fullName,
    String? idNo,
    int? deanId,
    int? courseId,
  }) async {
    setState(() => _isLoading = true);

    final res = await ApiService.updateStudentProfile(
      studentId: widget.studentId,
      fullName: fullName,
      idNo: idNo,
      deanId: deanId,
      courseId: courseId,
    );

    if (!mounted) return;

    if (res['status'] == 'success') {
      final data = res['data'] ?? {};
      final deanName = data['dean_name'] ?? 'Assigned Dean / Officer';

      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          title: const Row(
            children: [
              Icon(Icons.check_circle, color: Color(0xFF2E7D32)),
              SizedBox(width: 8),
              Text('Profile Updated', style: TextStyle(color: primaryNavy, fontSize: 16, fontWeight: FontWeight.bold)),
            ],
          ),
          content: Text(
            'Your account details have been saved.\n\n• Assigned Dean / Officer: $deanName\n\nYour profile and Dean tracking records have been updated automatically.',
            style: const TextStyle(fontSize: 13, height: 1.4),
          ),
          actions: [
            ElevatedButton(
              style: ElevatedButton.styleFrom(backgroundColor: primaryNavy),
              onPressed: () => Navigator.pop(ctx),
              child: const Text('OK', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      );

      await _fetchProfile();
    } else {
      setState(() => _isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Failed to update profile details.'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }
}
