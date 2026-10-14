from config import get_db_connection


_attendance_table_ready = False


def ensure_attendance_table():
	global _attendance_table_ready
	if _attendance_table_ready:
		return

	conn = get_db_connection()
	cursor = conn.cursor()
	cursor.execute(
		"""
		CREATE TABLE IF NOT EXISTS cit_attendance (
			id INT AUTO_INCREMENT PRIMARY KEY,
			event_id INT NOT NULL,
			student_id INT NOT NULL,
			status VARCHAR(32) NOT NULL,
			timestamp DATETIME NOT NULL,
			INDEX (event_id),
			INDEX (student_id)
		)
		"""
	)
	conn.commit()
	cursor.close()
	conn.close()
	_attendance_table_ready = True


def record_attendance(event_id, student_id, status, timestamp):
	ensure_attendance_table()
	conn = get_db_connection()
	cursor = conn.cursor()
	cursor.execute(
		"""
		INSERT INTO cit_attendance (event_id, student_id, status, timestamp)
		VALUES (%s,%s,%s,%s)
		""",
		(event_id, student_id, status, timestamp)
	)
	conn.commit()
	conn.close()


def attendance_exists(event_id, student_id, status):
	ensure_attendance_table()
	conn = get_db_connection()
	cursor = conn.cursor()
	cursor.execute(
		"""
		SELECT 1
		FROM cit_attendance
		WHERE event_id=%s AND student_id=%s AND status=%s
		LIMIT 1
		""",
		(event_id, student_id, status)
	)
	exists = cursor.fetchone() is not None
	conn.close()
	return exists


def get_attendance_by_event(event_id):
	ensure_attendance_table()
	conn = get_db_connection()
	cursor = conn.cursor(dictionary=True)
	cursor.execute(
		"""
		SELECT a.*, s.firstname, s.lastname, s.course, s.year_level
		FROM cit_attendance a
		JOIN students s ON s.id=a.student_id
		WHERE a.event_id=%s
		ORDER BY a.timestamp DESC
		""",
		(event_id,)
	)
	rows = cursor.fetchall()
	conn.close()
	return rows


def get_attendance_for_student(student_id):
	ensure_attendance_table()
	conn = get_db_connection()
	cursor = conn.cursor(dictionary=True)
	cursor.execute(
		"""
		SELECT a.*, e.event_name as event_name, e.event_date as event_date
		FROM cit_attendance a
		JOIN events e ON e.event_id=a.event_id
		WHERE a.student_id=%s
		ORDER BY e.event_date DESC
		""",
		(student_id,)
	)
	rows = cursor.fetchall()
	conn.close()
	return rows


def get_attendance(course=None, year_level=None, event_id=None):
	ensure_attendance_table()
	conn = get_db_connection()
	cursor = conn.cursor(dictionary=True)
	base = """
		SELECT a.*, s.firstname, s.lastname, s.course, s.year_level, e.event_name as event_name, e.event_date as event_date
		FROM cit_attendance a
		JOIN students s ON s.id=a.student_id
		LEFT JOIN events e ON e.event_id=a.event_id
	"""
	where_clauses = []
	params = []
	if event_id:
		where_clauses.append('a.event_id=%s')
		params.append(event_id)
	if course:
		where_clauses.append('s.course=%s')
		params.append(course)
	if year_level:
		where_clauses.append('s.year_level=%s')
		params.append(year_level)

	if where_clauses:
		base += ' WHERE ' + ' AND '.join(where_clauses)

	base += ' ORDER BY a.timestamp DESC'

	cursor.execute(base, tuple(params))
	rows = cursor.fetchall()
	conn.close()
	return rows


def get_sbc_dean_attendance_statuses(event_date):
	"""
	Fetches attendance logs and Dean verification status from SBC Internship Attendance System
	for a given event date.
	Returns a dictionary indexed by student_number, id_no, rfid_uid, and student_id.
	Used by CIT Department System to enforce that student attendance does NOT display Present
	unless confirmed by the Dean in the SBC system.
	"""
	if not event_date:
		return {}

	conn = get_db_connection()
	cursor = conn.cursor(dictionary=True)
	try:
		date_str = str(event_date)
		cursor.execute(
			"""
			SELECT 
				s.student_id AS sbc_student_id,
				s.student_number,
				s.id_no,
				s.rfid_uid,
				s.full_name,
				a.attendance_id,
				a.date,
				a.time_in_morning,
				a.time_out_morning,
				a.time_in_afternoon,
				a.time_out_afternoon,
				a.status,
				a.morning_status,
				a.afternoon_status,
				a.remarks,
				a.morning_remarks,
				a.afternoon_remarks
			FROM attendance a
			JOIN ojt o ON a.ojt_id = o.ojt_id
			JOIN student s ON o.student_id = s.student_id
			WHERE a.date = %s
			""",
			(date_str,)
		)
		rows = cursor.fetchall()
		dean_map = {}
		for r in rows:
			for key in (r.get('student_number'), r.get('id_no'), r.get('rfid_uid'), f"cit-{r.get('sbc_student_id')}"):
				if key:
					dean_map[str(key).strip().lower()] = r
		return dean_map
	except Exception:
		return {}
	finally:
		cursor.close()
		conn.close()