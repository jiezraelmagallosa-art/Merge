// SBC Internship Attendance System - Mobile App
// File: register_screen.dart

import 'package:flutter/material.dart';
import '../core/constants.dart';
import '../core/api_service.dart';

// Main widget / service implementation
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _fullNameController = TextEditingController();
  final _idNoController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _isLoading = false;
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  int _selectedCourseId = 1;
  List<Map<String, dynamic>> _courses = [
    {
      "course_id": 1,
      "course_code": "BSCS",
      "display_name": "BSCS - Bachelor of Science in Computer Science",
    },
    {
      "course_id": 2,
      "course_code": "BSIS",
      "display_name": "BSIS - Bachelor of Science in Information Systems",
    },
    {
      "course_id": 3,
      "course_code": "BLIS",
      "display_name": "BLIS - Bachelor of Library and Information Science",
    },
  ];

  int? _selectedDeanId;
  List<Map<String, dynamic>> _deans = [];

  String _selectedYearLevel = '4';
  final List<Map<String, String>> _yearLevels = [
    {"value": "1", "display_name": "1st Year"},
    {"value": "2", "display_name": "2nd Year"},
    {"value": "3", "display_name": "3rd Year"},
    {"value": "4", "display_name": "4th Year"},
  ];

  @override
  void initState() {
    super.initState();
    _fetchCourses();
    _fetchDeans();
  }

  Future<void> _fetchCourses() async {
    final result = await ApiService.getCourses();
    if (mounted && result['status'] == 'success' && result['data'] != null) {
      final List<dynamic> fetched = result['data'];
      if (fetched.isNotEmpty) {
        setState(() {
          _courses = fetched
              .map(
                (item) => {
                  "course_id": item['course_id'],
                  "course_code": item['course_code'],
                  "display_name": item['display_name'] ?? item['course_name'],
                },
              )
              .toList();
          _selectedCourseId = _courses.first['course_id'];
        });
      }
    }
  }

  Future<void> _fetchDeans() async {
    final result = await ApiService.getDeans();
    if (mounted && result['status'] == 'success' && result['data'] != null) {
      final List<dynamic> fetched = result['data'];
      if (fetched.isNotEmpty) {
        setState(() {
          _deans = fetched
              .map(
                (item) => {
                  "user_id": item['user_id'],
                  "display_name":
                      item['display_name'] ??
                      "${item['full_name']} (${item['email']})",
                },
              )
              .toList();
          _selectedDeanId = _deans.first['user_id'];
        });
      }
    }
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    if (_passwordController.text != _confirmPasswordController.text) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Passwords do not match!'),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

    if (_selectedDeanId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please select your specific Dean for account categorization.',
          ),
          backgroundColor: Colors.redAccent,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    final typedId = _idNoController.text.trim();
    final result = await ApiService.registerStudent(
      fullName: _fullNameController.text.trim(),
      idNo: typedId,
      studentNumber: typedId,
      email: _emailController.text.trim(),
      password: _passwordController.text.trim(),
      courseId: _selectedCourseId,
      deanId: _selectedDeanId,
      siteId: 1,
      yearLevel: _selectedYearLevel,
    );

    setState(() {
      _isLoading = false;
    });

    if (!mounted) return;

    if (result['status'] == 'success') {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Account created! Please log in.'),
          backgroundColor: AppColors.successGreen,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result['message'] ?? 'Registration failed. Please try again.',
          ),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  @override
  void dispose() {
    _fullNameController.dispose();
    _idNoController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primaryNavy,
      appBar: AppBar(
        backgroundColor: AppColors.primaryNavy.withValues(alpha: 0.85),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Student Registration',
          style: TextStyle(color: Colors.white, fontSize: 18),
        ),
      ),
      body: Stack(
        children: [

          Positioned.fill(
            child: Opacity(
              opacity: 0.45,
              child: Image.asset(
                'assets/images/sbc_building.jpg',
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) =>
                    Container(color: AppColors.primaryNavy),
              ),
            ),
          ),


          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 80,
                      height: 80,
                      padding: const EdgeInsets.all(4),
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: ClipOval(
                        child: Image.asset(
                          'assets/images/sbc_logo.png',
                          fit: BoxFit.contain,
                          errorBuilder: (context, error, stackTrace) => const Icon(
                            Icons.person_add_alt_1_rounded,
                            size: 60,
                            color: AppColors.accentGold,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Create Student Account',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Fill in your details to register for OJT Portal',
                      style: TextStyle(color: Colors.white70, fontSize: 13),
                    ),
                    const SizedBox(height: 24),


                    Container(
                      padding: const EdgeInsets.all(16.0),
                      decoration: const BoxDecoration(
                        color: Colors.transparent,
                      ),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [

                            TextFormField(
                              controller: _fullNameController,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Full Name',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.person_outline,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.trim().isEmpty) {
                                  return 'Please enter your full name';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),


                            TextFormField(
                              controller: _idNoController,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Student ID No.',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.card_membership_outlined,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.trim().isEmpty) {
                                  return 'Please enter your Student ID No.';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),


                            DropdownButtonFormField<String>(
                              initialValue: _selectedYearLevel,
                              isExpanded: true,
                              dropdownColor: AppColors.primaryNavy,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Year Level',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.layers_outlined,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              items: _yearLevels.map((lvl) {
                                return DropdownMenuItem<String>(
                                  value: lvl['value'],
                                  child: Text(
                                    lvl['display_name'] ?? '',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                );
                              }).toList(),
                              onChanged: (val) {
                                if (val != null) {
                                  setState(() {
                                    _selectedYearLevel = val;
                                  });
                                }
                              },
                            ),
                            const SizedBox(height: 14),


                            DropdownButtonFormField<int>(
                              initialValue: _selectedCourseId,
                              isExpanded: true,
                              dropdownColor: AppColors.primaryNavy,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Course Program',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.school_outlined,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              items: _courses.map((course) {
                                return DropdownMenuItem<int>(
                                  value: course['course_id'],
                                  child: Text(
                                    course['display_name'],
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                );
                              }).toList(),
                              onChanged: (val) {
                                if (val != null) {
                                  setState(() {
                                    _selectedCourseId = val;
                                  });
                                }
                              },
                            ),
                            const SizedBox(height: 14),


                            DropdownButtonFormField<int>(
                              initialValue: _selectedDeanId,
                              isExpanded: true,
                              dropdownColor: AppColors.primaryNavy,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Assigned Dean / Supervisor',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.supervisor_account_outlined,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              items: _deans.map((dean) {
                                return DropdownMenuItem<int>(
                                  value: dean['user_id'],
                                  child: Text(
                                    dean['display_name'],
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 13,
                                      fontWeight: FontWeight.w500,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                );
                              }).toList(),
                              onChanged: (val) {
                                if (val != null) {
                                  setState(() {
                                    _selectedDeanId = val;
                                  });
                                }
                              },
                            ),
                            const SizedBox(height: 14),


                            TextFormField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Institutional Email',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.email_outlined,
                                  color: AppColors.accentGold,
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.trim().isEmpty) {
                                  return 'Please enter your email';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),


                            TextFormField(
                              controller: _passwordController,
                              obscureText: _obscurePassword,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Password',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.lock_outline,
                                  color: AppColors.accentGold,
                                ),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscurePassword
                                        ? Icons.visibility_off
                                        : Icons.visibility,
                                    color: Colors.white70,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _obscurePassword = !_obscurePassword;
                                    });
                                  },
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Please enter a password';
                                }
                                if (value.length < 6) {
                                  return 'Password must be at least 6 characters';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 14),


                            TextFormField(
                              controller: _confirmPasswordController,
                              obscureText: _obscureConfirmPassword,
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w500),
                              decoration: InputDecoration(
                                labelText: 'Confirm Password',
                                labelStyle: const TextStyle(
                                  color: Colors.white70,
                                  fontWeight: FontWeight.w600,
                                ),
                                floatingLabelStyle: const TextStyle(
                                  color: AppColors.accentGold,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                ),
                                filled: true,
                                fillColor: Colors.transparent,
                                prefixIcon: const Icon(
                                  Icons.lock_reset_outlined,
                                  color: AppColors.accentGold,
                                ),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscureConfirmPassword
                                        ? Icons.visibility_off
                                        : Icons.visibility,
                                    color: Colors.white70,
                                  ),
                                  onPressed: () {
                                    setState(() {
                                      _obscureConfirmPassword =
                                          !_obscureConfirmPassword;
                                    });
                                  },
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: Colors.white70, width: 1.5),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(color: AppColors.accentGold, width: 2),
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'Please confirm your password';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 24),


                            ElevatedButton(
                              onPressed: _isLoading ? null : _handleRegister,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primaryNavy,
                                padding: const EdgeInsets.symmetric(
                                  vertical: 14,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                              ),
                              child: _isLoading
                                  ? const SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(
                                        color: Colors.white,
                                        strokeWidth: 2,
                                      ),
                                    )
                                  : const Text(
                                      'REGISTER ACCOUNT',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.white,
                                      ),
                                    ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),


                    TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text(
                        'Already have an account? Log In',
                        style: TextStyle(
                          color: AppColors.accentGold,
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
