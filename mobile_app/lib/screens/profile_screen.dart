// SBC Internship Attendance System - Mobile App
// File: profile_screen.dart

import 'package:flutter/material.dart';
import '../core/api_service.dart';
import '../core/constants.dart';
import 'login_screen.dart';

// Main widget / service implementation
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
    final String siteName = _profileData['site_name'] ?? 'SBC IT Department';
    final String siteLocation = _profileData['site_location'] ?? 'M\'lang, Cotabato';
    final String ojtNo = _profileData['ojt_no'] ?? 'OJT-2026-01';

    final int renderedHours = _profileData['rendered_hours'] ?? 0;
    final int requiredHours = _profileData['required_hours'] ?? 480;
    final double progress = (renderedHours / requiredHours).clamp(0.0, 1.0);

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

              // Edit Profile & Assignments Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _showEditAccountDialog,
                  icon: const Icon(Icons.manage_accounts_rounded, size: 18),
                  label: const Text(
                    'Edit Profile & Assignment Settings',
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


              _buildInfoCard(
                title: 'Academic Program',
                icon: Icons.school_outlined,
                items: [
                  {'label': 'Course', 'value': '$courseCode - $courseName'},
                  {'label': 'Institutional ID', 'value': _profileData['id_no'] ?? 'N/A'},
                ],
              ),
              const SizedBox(height: 12),


              _buildInfoCard(
                title: 'Assigned Dean / Supervisor',
                icon: Icons.supervisor_account_outlined,
                items: [
                  {'label': 'Dean Name', 'value': _profileData['dean_name'] ?? 'Unassigned'},
                  if (_profileData['dean_email'] != null && _profileData['dean_email'].toString().isNotEmpty)
                    {'label': 'Dean Email', 'value': _profileData['dean_email']},
                ],
              ),
              const SizedBox(height: 12),


              _buildTrainingSiteCard(
                ojtNo: ojtNo,
                siteName: siteName,
                siteLocation: siteLocation,
              ),
              const SizedBox(height: 12),

              _buildSiteBreakdownCard(requiredHours),
              const SizedBox(height: 12),


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
                      const Row(
                        children: [
                          Icon(Icons.timelapse_rounded, color: primaryNavy),
                          SizedBox(width: 8),
                          Text(
                            'Overall Progress Summary',
                            style: TextStyle(
                              fontSize: 16,
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
                          const Text('Consumed Hours:', style: TextStyle(color: Colors.black54)),
                          Text(
                            '${_profileData['formatted_rendered_time'] ?? "$renderedHours hrs"} / $requiredHours hrs',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: primaryNavy,
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      LinearProgressIndicator(
                        value: progress,
                        minHeight: 10,
                        backgroundColor: Colors.grey.shade200,
                        valueColor: const AlwaysStoppedAnimation<Color>(accentGold),
                        borderRadius: BorderRadius.circular(5),
                      ),
                      const SizedBox(height: 6),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            '${(progress * 100).toStringAsFixed(1)}% Completed',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: progress >= 1.0
                                  ? const Color(0xFF2E7D32)
                                  : accentGold,
                            ),
                          ),
                          Text(
                            'Goal: $requiredHours Hours',
                            style: const TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ],
                      ),
                      if (progress >= 1.0) ...[
                        const SizedBox(height: 14),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF8E1),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: accentGold.withValues(alpha: 0.5),
                            ),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.emoji_events_rounded,
                                color: accentGold,
                                size: 28,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      '🎉 Goal Hours Completed!',
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.bold,
                                        color: primaryNavy,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      _profileData['completion_message'] ??
                                          'Congratulations! You have completed your required $requiredHours goal hours.',
                                      style: const TextStyle(
                                        fontSize: 11,
                                        color: Colors.black87,
                                        height: 1.3,
                                      ),
                                    ),
                                  ],
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

              const SizedBox(height: 24),


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

  Widget _buildTrainingSiteCard({
    required String ojtNo,
    required String siteName,
    required String siteLocation,
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
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.business_outlined, color: primaryNavy),
                    SizedBox(width: 8),
                    Text(
                      'Training Site Assignment',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: primaryNavy,
                      ),
                    ),
                  ],
                ),
                TextButton.icon(
                  onPressed: _showTransferSiteDialog,
                  style: TextButton.styleFrom(
                    foregroundColor: primaryNavy,
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    visualDensity: VisualDensity.compact,
                  ),
                  icon: const Icon(Icons.edit_location_alt_outlined, size: 16, color: primaryNavy),
                  label: const Text(
                    'Edit Site',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const Divider(height: 16),
            _buildDetailRow('OJT Designation', ojtNo),
            const SizedBox(height: 6),
            _buildDetailRow('Active Facility', siteName),
            const SizedBox(height: 6),
            _buildDetailRow('Site Location', siteLocation),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 120,
          child: Text(
            label,
            style: const TextStyle(color: Colors.black54, fontSize: 13),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w600,
              color: AppColors.textDark,
              fontSize: 13,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSiteBreakdownCard(int requiredHours) {
    final List<dynamic> siteBreakdown = _profileData['site_breakdown'] as List<dynamic>? ?? [];

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
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Icon(Icons.history_toggle_off_rounded, color: primaryNavy),
                    SizedBox(width: 8),
                    Text(
                      'Training Facilities & Hours',
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
                    color: accentGold.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '${siteBreakdown.length} ${siteBreakdown.length == 1 ? "Site" : "Sites"}',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF8C6600),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 4),
            const Text(
              'Hours from previous training sites remain permanently preserved. Total time accumulation continues at your current location.',
              style: TextStyle(fontSize: 11, color: Colors.black54, height: 1.3),
            ),
            const Divider(height: 20),

            if (siteBreakdown.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 8.0),
                child: Text(
                  'Active Site: ${_profileData['site_name'] ?? 'SBC IT Department'} (${_profileData['formatted_rendered_time'] ?? '0 hrs 0 mins'})',
                  style: const TextStyle(fontSize: 13, color: Colors.black87),
                ),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: siteBreakdown.length,
                separatorBuilder: (context, index) => const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8.0),
                  child: Divider(height: 1, thickness: 0.8),
                ),
                itemBuilder: (context, index) {
                  final item = siteBreakdown[index];
                  final bool isCurrent = item['is_current'] == true;
                  final String label = item['label'] ?? (isCurrent ? 'Current Location' : 'Previous Location');
                  final String formattedTime = item['formatted_time'] ?? '0 hrs 0 mins';
                  final String siteName = item['site_name'] ?? 'Training Facility';
                  final String location = item['location'] ?? '';

                  return Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isCurrent
                          ? const Color(0xFFF0FDF4)
                          : const Color(0xFFF9FAFB),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isCurrent
                            ? const Color(0xFF86EFAC)
                            : Colors.grey.shade300,
                        width: isCurrent ? 1.5 : 1.0,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Text(
                                siteName,
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                  color: isCurrent ? primaryNavy : Colors.black87,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                color: isCurrent
                                    ? const Color(0xFF2E7D32)
                                    : const Color(0xFF78909C),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    isCurrent
                                        ? Icons.check_circle_rounded
                                        : Icons.history_rounded,
                                    color: Colors.white,
                                    size: 11,
                                  ),
                                  const SizedBox(width: 4),
                                  Text(
                                    label.toUpperCase(),
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 9.5,
                                      letterSpacing: 0.4,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        if (location.isNotEmpty) ...[
                          const SizedBox(height: 2),
                          Text(
                            location,
                            style: TextStyle(
                              fontSize: 11,
                              color: isCurrent ? Colors.black54 : Colors.grey.shade600,
                            ),
                          ),
                        ],
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: isCurrent
                                  ? const Color(0xFFBBF7D0)
                                  : Colors.grey.shade200,
                            ),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                isCurrent
                                    ? 'Current Site Logged Hours:'
                                    : 'Hours from Earlier Location:',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: isCurrent ? const Color(0xFF166534) : Colors.black54,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                              Text(
                                formattedTime,
                                style: TextStyle(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.bold,
                                  color: isCurrent ? const Color(0xFF166534) : Colors.blueGrey.shade800,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),

            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: primaryNavy.withValues(alpha: 0.05),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Total Accumulated Time:',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                      color: primaryNavy,
                    ),
                  ),
                  Text(
                    '${_profileData['formatted_rendered_time'] ?? "0 hrs 0 mins"} / $requiredHours hrs',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: primaryNavy,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showTransferSiteDialog() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(
        child: CircularProgressIndicator(color: primaryNavy),
      ),
    );

    final sitesRes = await ApiService.getSites();
    if (!mounted) return;
    Navigator.pop(context);

    if (sitesRes['status'] != 'success' || sitesRes['data'] == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(sitesRes['message'] ?? 'Unable to fetch training sites.'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

    final List<dynamic> allSites = sitesRes['data'];
    int? selectedSiteId;
    final int currentSiteId = int.tryParse(_profileData['site_id']?.toString() ?? '') ?? 1;

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
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.swap_horiz_rounded, color: primaryNavy, size: 24),
                          SizedBox(width: 8),
                          Text(
                            'Transfer Training Site',
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
                  const Text(
                    'Select your new partner training facility added by your Dean. Your accumulated hours from your earlier location will stay recorded, and new hours will accumulate at your new site.',
                    style: TextStyle(fontSize: 12, color: Colors.black54, height: 1.3),
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Available Partner Facilities:',
                    style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: primaryNavy),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    constraints: const BoxConstraints(maxHeight: 250),
                    child: ListView.builder(
                      shrinkWrap: true,
                      itemCount: allSites.length,
                      itemBuilder: (context, index) {
                        final site = allSites[index];
                        final sId = int.tryParse(site['site_id'].toString()) ?? 0;
                        final isCurrent = (sId == currentSiteId);
                        final isSelected = (sId == selectedSiteId);

                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          decoration: BoxDecoration(
                            border: Border.all(
                              color: isSelected
                                  ? primaryNavy
                                  : (isCurrent ? Colors.amber.shade400 : Colors.grey.shade300),
                              width: isSelected ? 2 : 1,
                            ),
                            borderRadius: BorderRadius.circular(10),
                            color: isSelected
                                ? primaryNavy.withValues(alpha: 0.05)
                                : (isCurrent ? Colors.amber.shade50 : Colors.white),
                          ),
                          child: InkWell(
                            borderRadius: BorderRadius.circular(10),
                            onTap: isCurrent
                                ? null
                                : () {
                                    setModalState(() {
                                      selectedSiteId = sId;
                                    });
                                  },
                            child: Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              child: Row(
                                children: [
                                  Icon(
                                    isSelected
                                        ? Icons.radio_button_checked
                                        : (isCurrent
                                            ? Icons.check_circle
                                            : Icons.radio_button_off),
                                    color: isSelected
                                        ? primaryNavy
                                        : (isCurrent ? Colors.amber.shade800 : Colors.grey),
                                    size: 20,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          site['site_name'] ?? 'Facility',
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13,
                                            color: isCurrent ? Colors.black54 : AppColors.textDark,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${site['location'] ?? ''} ${isCurrent ? '• (Current Site)' : ''}',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: isCurrent ? Colors.amber.shade900 : Colors.black54,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 16),
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
                        'CONFIRM SITE TRANSFER',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                      ),
                      onPressed: selectedSiteId == null
                          ? null
                          : () async {
                              Navigator.pop(context);
                              _handleSiteTransfer(selectedSiteId!);
                            },
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _handleSiteTransfer(int newSiteId) async {
    setState(() {
      _isLoading = true;
    });

    final res = await ApiService.updateStudentSite(
      studentId: widget.studentId,
      newSiteId: newSiteId,
    );

    if (!mounted) return;

    if (res['status'] == 'success') {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Training site updated successfully!'),
          backgroundColor: const Color(0xFF2E7D32),
        ),
      );
      await _fetchProfile();
    } else {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res['message'] ?? 'Failed to update training site.'),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
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
      ApiService.getSites(),
      ApiService.getCourses(),
    ]);

    if (!mounted) return;
    Navigator.pop(context);

    final deansRes = results[0];
    final sitesRes = results[1];
    final coursesRes = results[2];

    final List<dynamic> deans = (deansRes['status'] == 'success') ? (deansRes['data'] ?? []) : [];
    final List<dynamic> sites = (sitesRes['status'] == 'success') ? (sitesRes['data'] ?? []) : [];
    final List<dynamic> courses = (coursesRes['status'] == 'success') ? (coursesRes['data'] ?? []) : [];

    final nameCtrl = TextEditingController(text: _profileData['full_name'] ?? widget.studentName);
    final idNoCtrl = TextEditingController(text: _profileData['id_no'] ?? '');

    int? selectedDeanId = int.tryParse(_profileData['dean_id']?.toString() ?? '');
    int? selectedSiteId = int.tryParse(_profileData['site_id']?.toString() ?? '');
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
                            Icon(Icons.manage_accounts_rounded, color: primaryNavy, size: 24),
                            SizedBox(width: 8),
                            Text(
                              'Edit Profile & Assignments',
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
                              'Re-assigning your Dean automatically moves you to their active student roster. Changing your training site transfers your facility while keeping completed hours intact.',
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
                    const Text('Assigned Dean / Department', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
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

                    // Partner Training Facility Dropdown
                    const Text('Partner Training Facility', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: primaryNavy)),
                    const SizedBox(height: 4),
                    DropdownButtonFormField<int>(
                      value: sites.any((s) => int.tryParse(s['site_id'].toString()) == selectedSiteId) ? selectedSiteId : null,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      ),
                      hint: const Text('Select Training Site', style: TextStyle(fontSize: 12)),
                      items: sites.map<DropdownMenuItem<int>>((s) {
                        final sId = int.tryParse(s['site_id'].toString()) ?? 0;
                        return DropdownMenuItem<int>(
                          value: sId,
                          child: Text(
                            s['site_name'] ?? 'Facility',
                            style: const TextStyle(fontSize: 12.5),
                            overflow: TextOverflow.ellipsis,
                          ),
                        );
                      }).toList(),
                      onChanged: (val) {
                        setModalState(() {
                          selectedSiteId = val;
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
                            siteId: selectedSiteId,
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
    int? siteId,
    int? courseId,
  }) async {
    setState(() => _isLoading = true);

    final res = await ApiService.updateStudentProfile(
      studentId: widget.studentId,
      fullName: fullName,
      idNo: idNo,
      deanId: deanId,
      siteId: siteId,
      courseId: courseId,
    );

    if (!mounted) return;

    if (res['status'] == 'success') {
      final data = res['data'] ?? {};
      final deanName = data['dean_name'] ?? 'Assigned Dean';
      final siteName = data['site_name'] ?? 'Training Site';

      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          title: const Row(
            children: [
              Icon(Icons.check_circle, color: Color(0xFF2E7D32)),
              SizedBox(width: 8),
              Text('Assignments Updated', style: TextStyle(color: primaryNavy, fontSize: 16, fontWeight: FontWeight.bold)),
            ],
          ),
          content: Text(
            'Your account details have been saved.\n\n• Assigned Dean: $deanName\n• Active Facility: $siteName\n\nYour profile and Dean tracking records have been updated automatically.',
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
