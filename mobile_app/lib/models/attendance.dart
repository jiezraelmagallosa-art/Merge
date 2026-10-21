// SBC Internship Attendance System - Mobile App
// File: attendance.dart

// Main widget / service implementation
class AttendanceModel {
  final int? attendanceId;
  final String studentId;
  final String date;
  final String timeIn;
  final String timeOut;
  final double latitude;
  final double longitude;
  final String selfiePath;
  final String syncStatus;

  AttendanceModel({
    this.attendanceId,
    required this.studentId,
    required this.date,
    required this.timeIn,
    this.timeOut = '',
    this.latitude = 0.0,
    this.longitude = 0.0,
    this.selfiePath = '',
    this.syncStatus = 'Pending',
  });

  Map<String, dynamic> toMap() {
    return {
      if (attendanceId != null) 'attendance_id': attendanceId,
      'student_id': studentId,
      'date': date,
      'time_in': timeIn,
      'time_out': timeOut,
      'latitude': latitude,
      'longitude': longitude,
      'selfie_path': selfiePath,
      'sync_status': syncStatus,
    };
  }

  factory AttendanceModel.fromMap(Map<String, dynamic> map) {
    return AttendanceModel(
      attendanceId: map['attendance_id'] != null
          ? int.tryParse(map['attendance_id'].toString())
          : null,
      studentId: map['student_id']?.toString() ?? '',
      date: map['date'] ?? '',
      timeIn: map['time_in'] ?? '',
      timeOut: map['time_out'] ?? '',
      latitude: (map['latitude'] is num) ? (map['latitude'] as num).toDouble() : double.tryParse(map['latitude']?.toString() ?? '0') ?? 0.0,
      longitude: (map['longitude'] is num) ? (map['longitude'] as num).toDouble() : double.tryParse(map['longitude']?.toString() ?? '0') ?? 0.0,
      selfiePath: map['selfie_path'] ?? '',
      syncStatus: map['sync_status'] ?? 'Pending',
    );
  }

  factory AttendanceModel.fromJson(Map<String, dynamic> json) {
    return AttendanceModel(
      attendanceId: json['attendance_id'] != null ? int.tryParse(json['attendance_id'].toString()) : json['id'] != null ? int.tryParse(json['id'].toString()) : null,
      studentId: (json['student_id'] ?? json['ojt_id'] ?? '').toString(),
      date: json['date'] ?? json['attendance_date'] ?? '',
      timeIn: json['login_time'] ?? json['time_in'] ?? json['time_in_morning'] ?? '',
      timeOut: json['logout_time'] ?? json['time_out'] ?? json['time_out_afternoon'] ?? '',
      latitude: double.tryParse(json['latitude']?.toString() ?? '0.0') ?? 0.0,
      longitude: double.tryParse(json['longitude']?.toString() ?? '0.0') ?? 0.0,
      selfiePath: json['selfie_path'] ?? json['image_path'] ?? '',
      syncStatus: json['sync_status'] ?? 'Synced',
    );
  }

  AttendanceModel copyWith({
    int? attendanceId,
    String? studentId,
    String? date,
    String? timeIn,
    String? timeOut,
    double? latitude,
    double? longitude,
    String? selfiePath,
    String? syncStatus,
  }) {
    return AttendanceModel(
      attendanceId: attendanceId ?? this.attendanceId,
      studentId: studentId ?? this.studentId,
      date: date ?? this.date,
      timeIn: timeIn ?? this.timeIn,
      timeOut: timeOut ?? this.timeOut,
      latitude: latitude ?? this.latitude,
      longitude: longitude ?? this.longitude,
      selfiePath: selfiePath ?? this.selfiePath,
      syncStatus: syncStatus ?? this.syncStatus,
    );
  }
}
