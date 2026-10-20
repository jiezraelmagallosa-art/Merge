/**
 * SBC Dean's Administrative Suite - Client Application Logic
 * Handles authentication, real-time KPI metrics, attendance logs, and site management.
 */



// Global API Base Configuration
const API_BASE = '../backend/';

// Student Photo Verification Fallback (Institutional SBC Vector Avatar)
const DEFAULT_STUDENT_AVATAR_FALLBACK = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MDAgMzAwIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0ic2JjQmdHcmFkIiB4MT0iMCUiIHkxPSIwJSIgeDI9IjEwMCUiIHkyPSIxMDAlIj4KICAgICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzA3MWIzMCIvPgogICAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMxNDJjNGMiLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9InNiY0F2YXRhckdyYWQiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMCUiIHkyPSIxMDAlIj4KICAgICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzk0YTNiOCIvPgogICAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiM2NDc0OGIiLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSJ1cmwoI3NiY0JnR3JhZCkiLz4KCiAgPCEtLSBTdWJ0bGUgaW5zdGl0dXRpb25hbCBncmlkIHBhdHRlcm4gLS0+CiAgPGNpcmNsZSBjeD0iMjAwIiBjeT0iMTEwIiByPSI2OCIgZmlsbD0iIzAwMmQ1NiIgZmlsbC1vcGFjaXR5PSIwLjQiIHN0cm9rZT0iIzM4YmRmOCIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1kYXNoYXJyYXk9IjQgNCIgc3Ryb2tlLW9wYWNpdHk9IjAuNSIvPgoKICA8IS0tIFN0dWRlbnQgU2lsaG91ZXR0ZSAtLT4KICA8IS0tIEhlYWQgLS0+CiAgPGNpcmNsZSBjeD0iMjAwIiBjeT0iOTQiIHI9IjMwIiBmaWxsPSJ1cmwoI3NiY0F2YXRhckdyYWQpIi8+CiAgPCEtLSBUb3JzbyAmIENvbGxlZ2UgVW5pZm9ybSBQb2xvIC0tPgogIDxwYXRoIGQ9Ik0xNDQgMTYyIEMxNDQgMTM0LCAxNzIgMTMwLCAyMDAgMTMwIEMyMjggMTMwLCAyNTYgMTM0LCAyNTYgMTYyIEMyNTYgMTcwLCAyNTIgMTc0LCAyNDQgMTc0IEwxNTYgMTc0IEMxNDggMTc0LCAxNDQgMTcwLCAxNDQgMTYyIFoiIGZpbGw9InVybCgjc2JjQXZhdGFyR3JhZCkiLz4KICAKICA8IS0tIENvbGxlZ2UgSUQgTGFueWFyZCAmIEJhZGdlIC0tPgogIDxwYXRoIGQ9Ik0xOTAgMTMwIEwxOTUgMTUwIEwyMDUgMTUwIEwyMTAgMTMwIiBzdHJva2U9IiNmNTllMGIiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPgogIDxyZWN0IHg9IjE5MyIgeT0iMTQ4IiB3aWR0aD0iMTQiIGhlaWdodD0iMjAiIHJ4PSIyIiBmaWxsPSIjMDI4NGM3Ii8+CiAgPHJlY3QgeD0iMTk1IiB5PSIxNTAiIHdpZHRoPSIxMCIgaGVpZ2h0PSI1IiByeD0iMSIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjkiLz4KCiAgPCEtLSBWZXJpZmljYXRpb24gQ2FtZXJhIEljb24gQmFkZ2UgLS0+CiAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjMwLCA3MikiPgogICAgPGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTQiIGZpbGw9IiMwMjg0YzciIHN0cm9rZT0iI2ZmZmZmZiIgc3Ryb2tlLXdpZHRoPSIyIi8+CiAgICA8cGF0aCBkPSJNNyAxNSBMMTcgMTUgQzE3LjYgMTUgMTggMTQuNiAxOCAxNCBMMTggMTAgQzE4IDkuNCAxNy42IDkgMTcgOSBMMTUgOSBMMTQgNyBMMTAgNyBMOSA5IEw3IDkgQzYuNCA5IDYgOS40IDYgMTAgTDYgMTQgQzYgMTQuNiA2LjQgMTUgNyAxNSBaIiBmaWxsPSIjZmZmZmZmIi8+CiAgICA8Y2lyY2xlIGN4PSIxMiIgY3k9IjEyIiByPSIyLjUiIGZpbGw9IiMwMjg0YzciLz4KICA8L2c+CgogIDwhLS0gQ2xlYW4gSW5zdGl0dXRpb25hbCBMYWJlbHMgLS0+CiAgPHRleHQgeD0iMjAwIiB5PSIyMTAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiNmOGZhZmMiIGZvbnQtZmFtaWx5PSItYXBwbGUtc3lzdGVtLCBCbGlua01hY1N5c3RlbUZvbnQsICdTZWdvZSBVSScsIFJvYm90bywgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxMyIgZm9udC13ZWlnaHQ9IjcwMCIgbGV0dGVyLXNwYWNpbmc9IjEiPlNPVVRIRVJOIEJBUFRJU1QgQ09MTEVHRTwvdGV4dD4KICA8dGV4dCB4PSIyMDAiIHk9IjIzMCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzM4YmRmOCIgZm9udC1mYW1pbHk9Ii1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgJ1NlZ29lIFVJJywgUm9ib3RvLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjExIiBmb250LXdlaWdodD0iNjAwIiBsZXR0ZXItc3BhY2luZz0iMC41Ij5TVFVERU5UIFBIT1RPIFZFUklGSUNBVElPTjwvdGV4dD4KICA8dGV4dCB4PSIyMDAiIHk9IjI1NCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzk0YTNiOCIgZm9udC1mYW1pbHk9Ii1hcHBsZS1zeXN0ZW0sIEJsaW5rTWFjU3lzdGVtRm9udCwgJ1NlZ29lIFVJJywgUm9ib3RvLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwLjUiPkxpdmUgQ2FwdHVyZSBJbWFnZSBQZW5kaW5nIG9yIFB1cmdlZDwvdGV4dD4KPC9zdmc+';
const DEFAULT_STUDENT_AVATAR = DEFAULT_STUDENT_AVATAR_FALLBACK;

function resolvePhotoUrl(fullUrl, imagePath) {
  if (imagePath && typeof imagePath === 'string' && imagePath.trim().length > 0) {
    const clean = imagePath.trim().replace(/^(\.\.\/|\/)+/, '');
    return '../' + clean;
  }
  if (fullUrl && typeof fullUrl === 'string' && fullUrl.trim().length > 0) {
    if (window.location.protocol === 'https:' && fullUrl.startsWith('http://')) {
      return fullUrl.replace('http://', 'https://');
    }
    return fullUrl;
  }
  return DEFAULT_STUDENT_AVATAR;
}

function handlePhotoError(img, altFallbackUrl) {
  if (!img) return;
  if (img.dataset.hasFailed) return;

  // If we haven't tried the alternative URL (e.g. absolute URL if relative failed)
  if (altFallbackUrl && img.src !== altFallbackUrl && !img.dataset.triedAlt) {
    img.dataset.triedAlt = '1';
    img.src = altFallbackUrl;
    return;
  }

  img.dataset.hasFailed = '1';
  img.onerror = null;
  img.src = DEFAULT_STUDENT_AVATAR_FALLBACK;
  img.style.objectFit = 'contain';
  img.style.backgroundColor = '#071b30';
}


// Cached Application State Variables
let currentUser = null;
let currentTab = 'overview';
let currentCourseFilter = 'ALL';
let currentLogsCourseFilter = 'ALL';
let currentLogsDateFilter = 'TODAY'; // Default to Today's data!
let currentLogsCustomDate = '';
let currentLogsStatusFilter = 'ALL';
let currentLogsSiteFilter = 'ALL';
let currentLogsYearLevelFilter = 'ALL';
let currentStudentStatusFilter = 'ALL';
let currentStudentSiteFilter = 'ALL';
let currentStudentYearLevelFilter = 'ALL';
let currentAbsenceStatusFilter = 'ALL';
let currentAbsenceCourseFilter = 'ALL';
let selectedReportCourse = 'ALL';
let selectedReportStatus = 'ALL';
let selectedReportSite = 'ALL';
let selectedReportYearLevel = 'ALL';
let cachedStudents = [];
let cachedLogs = [];
let cachedAbsences = [];
let cachedJournals = [];
let currentJournalCourseFilter = 'ALL';
let currentJournalDateFilter = 'TODAY'; // Default to Today's data!
let currentJournalCustomDate = '';
let currentJournalStatusFilter = 'ALL';
let currentJournalSiteFilter = 'ALL';
let currentJournalYearLevelFilter = 'ALL';
let currentActiveJournalId = null;
let cachedSites = [];
let cachedCourses = [];
let cachedLiveCaptures = [];
let currentModalPhotos = [];
let currentModalPhotoIndex = 0;
let currentLightboxGallery = null;
let currentLightboxIndex = -1;
let currentTagAttendanceId = null;
let currentTagShift = 'morning';
let taggedStudentIds = new Set();
let tagModalActivePhotos = [];
let tagPhotoSlots = [];
let activeSlidePhotos = [];
let currentSlidePhotoIndex = 0;

// UI Initialization & Main Event Listeners
document.addEventListener('DOMContentLoaded', () => {
  checkSession();
  setupEventListeners();
});

function checkSession() {
  const savedUser = localStorage.getItem('sbc_admin_user');
  if (savedUser) {
    currentUser = JSON.parse(savedUser);
    showAppShell();
    loadTabContent('overview');
  } else {
    showAuthScreen();
  }
}

function setupEventListeners() {

  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', handleLogin);
  }


  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', handleRegister);
  }


  const showRegisterBtn = document.getElementById('showRegisterBtn');
  if (showRegisterBtn) {
    showRegisterBtn.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('loginView').classList.add('hidden');
      document.getElementById('registerView').classList.remove('hidden');
    });
  }

  const showLoginBtn = document.getElementById('showLoginBtn');
  if (showLoginBtn) {
    showLoginBtn.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('registerView').classList.add('hidden');
      document.getElementById('loginView').classList.remove('hidden');
    });
  }


  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
      const targetTab = item.getAttribute('data-tab');
      if (targetTab) {
        e.preventDefault();
        switchTab(targetTab);
      }
    });
  });


  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch) {
    globalSearch.addEventListener('input', (e) => {
      filterActiveTable(e.target.value.trim().toLowerCase());
    });
  }

  // Global Keyboard Shortcuts (ESC to close modals/lightbox, Arrow keys for photo gallery)
  document.addEventListener('keydown', (e) => {
    const lightbox = document.getElementById('imageLightboxModal');
    if (lightbox && lightbox.classList.contains('active')) {
      if (e.key === 'Escape') {
        closeImageLightbox();
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      if (e.key === 'ArrowLeft') {
        navigateLightbox(-1);
        e.preventDefault();
      }
      if (e.key === 'ArrowRight') {
        navigateLightbox(1);
        e.preventDefault();
      }
      if (e.key === 'r' || e.key === 'R') {
        rotateLightbox(90);
        e.preventDefault();
      }
      if (e.key === 'l' || e.key === 'L') {
        rotateLightbox(-90);
        e.preventDefault();
      }
      if (e.key === 'm' || e.key === 'M') {
        toggleLightboxMirror();
        e.preventDefault();
      }
      if (e.key === '0') {
        resetLightboxTransform();
        e.preventDefault();
      }
      return;
    }

    if (e.key === 'Escape') {
      const photoModal = document.getElementById('photoModal');
      if (photoModal && photoModal.classList.contains('active')) {
        closePhotoModal();
      }
      const tagModal = document.getElementById('tagGroupModal');
      if (tagModal && tagModal.classList.contains('active')) {
        closeTagGroupModal();
      }
    }
  });
}

// Administrative Login Handler
async function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('adminEmail').value.trim();
  const password = document.getElementById('adminPassword').value.trim();
  const loginError = document.getElementById('loginError');

  loginError.classList.add('hidden');

  try {
    const res = await fetch(API_BASE + 'admin_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const data = await res.json();

    if (data.status === 'success') {
      currentUser = data.user;
      localStorage.setItem('sbc_admin_user', JSON.stringify(currentUser));
      showAppShell();
      loadTabContent('overview');
    } else {
      loginError.textContent = data.message || 'Invalid credentials';
      loginError.classList.remove('hidden');
    }
  } catch (err) {
    console.error('Login error:', err);
    loginError.textContent = 'Server connection error. Please try again.';
    loginError.classList.remove('hidden');
  }
}

// Account Registration Handler
async function handleRegister(e) {
  e.preventDefault();
  const full_name = document.getElementById('regFullName').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const role = document.getElementById('regRole').value;
  const password = document.getElementById('regPassword').value.trim();
  const registerMsg = document.getElementById('registerMsg');

  registerMsg.classList.add('hidden');

  try {
    const res = await fetch(API_BASE + 'admin_register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ full_name, email, role, password })
    });
    const data = await res.json();

    if (data.status === 'success') {
      registerMsg.textContent = data.message;
      registerMsg.className = 'badge badge-success';
      registerMsg.classList.remove('hidden');

      document.getElementById('adminEmail').value = email;
      document.getElementById('adminPassword').value = password;
      setTimeout(() => {
        document.getElementById('registerView').classList.add('hidden');
        document.getElementById('loginView').classList.remove('hidden');
      }, 1500);
    } else {
      registerMsg.textContent = data.message || 'Registration failed.';
      registerMsg.className = 'badge badge-danger';
      registerMsg.classList.remove('hidden');
    }
  } catch (err) {
    console.error('Registration error:', err);
    registerMsg.textContent = 'Server connection error. Please try again.';
    registerMsg.className = 'badge badge-danger';
    registerMsg.classList.remove('hidden');
  }
}

function logout() {
  if (confirm("Are you sure you want to sign out of the Dean's Administrative Suite?")) {
    localStorage.removeItem('sbc_admin_user');
    currentUser = null;
    const emailField = document.getElementById('adminEmail');
    const passField = document.getElementById('adminPassword');
    if (emailField) emailField.value = '';
    if (passField) passField.value = '';
    showAuthScreen();
  }
}

function showAuthScreen() {
  document.getElementById('authScreen').classList.remove('hidden');
  document.getElementById('appShell').classList.add('hidden');
}

function showAppShell() {
  document.getElementById('authScreen').classList.add('hidden');
  document.getElementById('appShell').classList.remove('hidden');
  if (currentUser) {
    document.getElementById('adminNameDisplay').textContent = currentUser.full_name || 'Dean Admin';
  }
  fetchLogs();
  fetchAbsences();
  fetchJournals();
  fetchSites();
  fetchCourses(false);
}

function switchTab(tabId) {
  if (tabId === 'sites') {
    tabId = 'overview';
  }
  currentTab = tabId;
  document.querySelectorAll('.nav-item').forEach(el => {
    el.classList.toggle('active', el.getAttribute('data-tab') === tabId);
  });

  document.querySelectorAll('.view-pane').forEach(pane => {
    pane.classList.add('hidden');
  });

  const activePane = document.getElementById(`view-${tabId}`);
  if (activePane) {
    activePane.classList.remove('hidden');
  }

  loadTabContent(tabId);
}

function loadTabContent(tabId) {
  switch (tabId) {
    case 'overview':
      fetchOverview();
      break;
    case 'students':
      fetchStudents();
      break;
    case 'logs':
      fetchLogs();
      break;
    case 'absences':
      fetchAbsences();
      break;
    case 'journals':
      fetchJournals();
      break;
    case 'courses':
      fetchCourses();
      break;
    case 'sites':
      fetchSites();
      break;
    case 'reports':
      fetchReports();
      break;
  }
}

function getDeanQueryParam() {
  if (currentUser && currentUser.role === 'Dean' && currentUser.user_id) {
    return '?dean_id=' + currentUser.user_id;
  }
  return '';
}


async function fetchOverview() {
  try {
    const res = await fetch(API_BASE + 'admin_get_overview.php' + getDeanQueryParam());
    const data = await res.json();
    if (data.status === 'success') {
      const kpis = data.kpis;
      document.getElementById('kpiActiveShifts').textContent = kpis.active_shifts_today || '0';
      document.getElementById('kpiTotalInterns').textContent = kpis.total_interns || '0';
      document.getElementById('kpiCompletionRate').textContent = `${kpis.completion_rate}%`;
      document.getElementById('kpiPendingAbsences').textContent = kpis.pending_absences || '0';
      document.getElementById('kpiTotalHours').textContent = `${kpis.total_rendered_hours} hrs`;

      renderLiveCaptures(data.recent_captures || []);
    }
  } catch (err) {
    console.error('Fetch overview error:', err);
  }
}

function renderLiveCaptures(captures) {
  const container = document.getElementById('liveCaptureFeed');
  if (!container) return;

  cachedLiveCaptures = captures || [];

  if (captures.length === 0) {
    container.innerHTML = `
      <div style="grid-column: 1 / -1; padding: 2rem; text-align: center; color: var(--text-muted);">
        No live verification photo captures recorded today yet.
      </div>`;
    return;
  }

  container.innerHTML = captures.map((c, idx) => {
    const photoSrc = resolvePhotoUrl(c.full_url, c.image_path);
    const altFallback = (c.full_url && c.full_url !== photoSrc) ? c.full_url : '';
    const attId = c.attendance_id || 0;
    return `
    <div class="capture-card" onclick="goToAttendanceLog(${attId}, ${idx})" title="Click to view &amp; evaluate this log in Attendance Logs">
      <div class="capture-img-box">
        <img src="${photoSrc}" alt="${escapeHtml(c.full_name)}" onerror="handlePhotoError(this, '${escapeHtml(altFallback)}');">
        <span class="capture-badge">${c.shift_type}</span>
        <div class="capture-zoom-overlay">📋 Review Log</div>
      </div>
      <div class="capture-info">
        <h4>${escapeHtml(c.full_name)}</h4>
        <p>${c.student_number} &bull; ${c.course_code}</p>
        <p style="margin-top: 0.2rem; font-weight: 600; color: var(--navy-primary);">${c.captured_time} (${c.date})</p>
        <div style="margin-top: 0.45rem;">
          <button type="button" class="btn btn-outline" style="font-size: 0.72rem; padding: 0.22rem 0.5rem; width: 100%; font-weight: 700; border-color: #38bdf8; color: #0284c7;" onclick="event.stopPropagation(); goToAttendanceLog(${attId}, ${idx});">
            Review Attendance Log &rarr;
          </button>
        </div>
      </div>
    </div>
  `;
  }).join('');
}

function goToAttendanceLog(attendanceId, captureIdx = null) {
  setLogsDateFilter('ALL');
  switchTab('logs');
  if (attendanceId) {
    setTimeout(() => {
      const log = (cachedLogs || []).find(l => String(l.attendance_id) === String(attendanceId));
      if (log && log.photos && log.photos.length > 0) {
        openPhotoModal(log.photos, attendanceId);
      }
    }, 200);
  }
}

// Student Tracking Data Loader
async function fetchStudents() {
  try {
    await fetchCourses(false);
    const deanP = getDeanQueryParam();
    const studentUrl = API_BASE + 'admin_get_students.php' + (deanP ? deanP + '&all=1' : '?all=1');
    const res = await fetch(studentUrl);
    const data = await res.json();
    if (data.status === 'success') {
      cachedStudents = data.data || [];
      populateDynamicCourseDropdowns();
      populateLogsSiteDropdown();
      filterByCourse(currentCourseFilter, false);
    }
  } catch (err) {
    console.error('Fetch students error:', err);
  }
}

function updateCourseCounts(counts) {
  populateDynamicCourseDropdowns();
}

function handleStudentFilterChange() {
  const courseEl = document.getElementById('courseSelectFilter');
  const statusEl = document.getElementById('studentStatusSelectFilter');
  const yearLevelEl = document.getElementById('studentYearLevelSelectFilter');
  const siteEl = document.getElementById('studentSiteSelectFilter');

  if (courseEl) currentCourseFilter = courseEl.value || 'ALL';
  if (statusEl) currentStudentStatusFilter = statusEl.value || 'ALL';
  if (yearLevelEl) currentStudentYearLevelFilter = yearLevelEl.value || 'ALL';
  if (siteEl) currentStudentSiteFilter = siteEl.value || 'ALL';

  applyStudentFilters();
}

function applyStudentFilters() {
  let filtered = cachedStudents || [];

  if (currentCourseFilter !== 'ALL') {
    filtered = filtered.filter(s => (s.course_code || '').toUpperCase() === currentCourseFilter.toUpperCase());
  }

  if (currentStudentStatusFilter !== 'ALL') {
    if (currentStudentStatusFilter === 'REGULAR') {
      filtered = filtered.filter(s => (s.total_absences || 0) === 0);
    } else if (currentStudentStatusFilter === 'IRREGULAR') {
      filtered = filtered.filter(s => (s.total_absences || 0) > 0);
    } else {
      filtered = filtered.filter(s => (s.status || '').toLowerCase() === currentStudentStatusFilter.toLowerCase());
    }
  }

  if (currentStudentYearLevelFilter !== 'ALL') {
    filtered = filtered.filter(s => {
      const sLvl = (s.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      const fLvl = currentStudentYearLevelFilter.toString().trim().replace(/[^0-9]/g, '');
      return sLvl === fLvl || (s.year_level || '').toString().toLowerCase().includes(currentStudentYearLevelFilter.toString().toLowerCase());
    });
  }

  if (currentStudentSiteFilter !== 'ALL') {
    filtered = filtered.filter(s => {
      const siteMatch = (s.site_id?.toString() === currentStudentSiteFilter.toString()) ||
        (s.site_name && s.site_name.trim().toLowerCase() === currentStudentSiteFilter.trim().toLowerCase());
      return siteMatch;
    });
  }

  renderStudentsTable(filtered);
}

function filterByCourse(courseCode, updateSelect = true) {
  currentCourseFilter = courseCode || 'ALL';

  const selectEl = document.getElementById('courseSelectFilter');
  if (selectEl && updateSelect) {
    selectEl.value = currentCourseFilter;
  }

  applyStudentFilters();
}

function getCourseBadgeClass(courseCode) {
  const code = (courseCode || '').toUpperCase();
  if (code === 'BSCS') return 'badge-bscs';
  if (code === 'BSIS') return 'badge-bsis';
  if (code === 'BLIS') return 'badge-blis';
  return 'badge-navy';
}

function formatYearLevelDisplay(val) {
  if (!val) return '4th Year';
  const str = val.toString().trim();
  const numOnly = str.replace(/[^0-9]/g, '');
  if (numOnly === '1') return '1st Year';
  if (numOnly === '2') return '2nd Year';
  if (numOnly === '3') return '3rd Year';
  if (numOnly === '4') return '4th Year';
  return str.length > 0 ? str : '4th Year';
}

function renderStudentsTable(students) {
  const tbody = document.getElementById('studentsTableBody');
  if (!tbody) return;

  if (students.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No students enrolled in this course category.</td></tr>`;
    return;
  }

  tbody.innerHTML = students.map(s => {
    const statusBadgeClass = s.status === 'Completed' ? 'badge-success' : 'badge-warning';
    const courseBadge = getCourseBadgeClass(s.course_code);
    const absences = s.total_absences || 0;
    const standingBadge = absences === 0
      ? '<span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-weight:700;">Regular (0 Absences)</span>'
      : `<span class="badge" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; font-weight:700;">Irregular (${absences} Abs)</span>`;

    return `
      <tr>
        <td>
          <div style="font-weight: 700; color: var(--navy-primary);">${escapeHtml(s.full_name)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.15rem;">
            <span>${s.student_number} &bull; ${s.id_no}</span>
            ${s.rfid_uid ? `<span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-size:0.68rem; padding:0.1rem 0.4rem; font-weight:700;" title="RFID Card UID: ${escapeHtml(s.rfid_uid)}">💳 RFID Mapped</span>` : `<span class="badge" style="background:#f8fafc; color:#94a3b8; border:1px solid #e2e8f0; font-size:0.68rem; padding:0.1rem 0.35rem;">No RFID</span>`}
          </div>
        </td>
        <td>
          <div>
            <span class="badge ${courseBadge}">${escapeHtml(s.course_code)}</span>
          </div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">${escapeHtml(s.course_name)}</div>
        </td>
        <td>
          <div style="font-weight: 700; color: var(--navy-primary); font-size: 0.95rem;">${formatYearLevelDisplay(s.year_level)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Undergraduate Student</div>
        </td>
        <td>
          <div style="font-weight: 700; color: #166534; font-size: 0.95rem;">${s.total_days || 0} Session(s)</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">Event Presence</div>
        </td>
        <td>${standingBadge}</td>

        <td>
          <div style="display:flex; gap:0.4rem; align-items:center;">
            <button class="btn btn-outline" style="padding:0.35rem 0.65rem; font-size:0.78rem;" onclick="openStudentDrawer('${s.student_id}')">Details</button>
            <button class="btn btn-outline" style="padding:0.35rem 0.65rem; font-size:0.78rem; border-color:#f59e0b; color:#d97706; font-weight:700;" onclick="openRfidMappingModal('${s.student_id}', '${escapeHtml(s.full_name).replace(/'/g, "\\'")}', '${s.student_number}', '${s.rfid_uid || ''}', '${escapeHtml(s.course_code)}')" title="Map School ID or RFID Card for instant tap in line">💳 Map RFID</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}


// Date helper for local ISO date YYYY-MM-DD
function getLocalDateISO(dateObj = new Date()) {
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// Attendance Verification Logs Fetcher
async function fetchLogs() {
  try {
    const res = await fetch(API_BASE + 'admin_get_logs.php' + getDeanQueryParam());
    const data = await res.json();
    if (data.status === 'success') {
      cachedLogs = data.data || [];
      const logDateInput = document.getElementById('logsCustomDateInput');
      if (logDateInput && !logDateInput.value) {
        logDateInput.value = getLocalDateISO();
      }

      // If current filter is 'TODAY' but there are no records for today while records exist,
      // default to 'ALL' so the Dean immediately sees the pending verification records!
      const todayISO = getLocalDateISO();
      const hasTodayLogs = cachedLogs.some(l => (l.raw_date || '').substring(0, 10) === todayISO);
      if (!hasTodayLogs && currentLogsDateFilter === 'TODAY' && cachedLogs.length > 0) {
        currentLogsDateFilter = 'ALL';
      }

      updateLogsCourseCounts(data.course_counts || {});
      populateLogsSiteDropdown();
      applyLogsFilters();
      updateNotifications();
    }
  } catch (err) {
    console.error('Fetch logs error:', err);
  }
}

function updateLogsCourseCounts(counts) {
  populateDynamicCourseDropdowns();
}

// Populate Partner Facility dropdown dynamically across all views
function populateLogsSiteDropdown() {
  const siteSet = new Set();

  (cachedSites || []).forEach(st => {
    if (st.site_name && st.site_name.trim()) siteSet.add(st.site_name.trim());
  });

  (cachedStudents || []).forEach(s => {
    if (s.site_name && s.site_name.trim()) siteSet.add(s.site_name.trim());
  });

  (cachedLogs || []).forEach(l => {
    if (l.site_name && l.site_name.trim()) siteSet.add(l.site_name.trim());
  });

  const sortedSites = Array.from(siteSet).sort((a, b) => a.localeCompare(b));

  // 1. Logs Filter Dropdown
  const logsSiteSelect = document.getElementById('logsSiteSelectFilter');
  if (logsSiteSelect) {
    let html = `<option value="ALL">All Partner Facilities</option>`;
    sortedSites.forEach(name => {
      const isSelected = name === currentLogsSiteFilter ? 'selected' : '';
      html += `<option value="${escapeHtml(name)}" ${isSelected}>${escapeHtml(name)}</option>`;
    });
    logsSiteSelect.innerHTML = html;
  }

  // 2. Student Tracking Filter Dropdown
  const studentSiteSelect = document.getElementById('studentSiteSelectFilter');
  if (studentSiteSelect) {
    let html = `<option value="ALL">All Partner Facilities</option>`;
    sortedSites.forEach(name => {
      const isSelected = name === currentStudentSiteFilter ? 'selected' : '';
      html += `<option value="${escapeHtml(name)}" ${isSelected}>${escapeHtml(name)}</option>`;
    });
    studentSiteSelect.innerHTML = html;
  }

  // 3. Journal Filter Dropdown
  const journalSiteSelect = document.getElementById('journalSiteSelectFilter');
  if (journalSiteSelect) {
    let html = `<option value="ALL">All Partner Facilities</option>`;
    sortedSites.forEach(name => {
      const isSelected = name === currentJournalSiteFilter ? 'selected' : '';
      html += `<option value="${escapeHtml(name)}" ${isSelected}>${escapeHtml(name)}</option>`;
    });
    journalSiteSelect.innerHTML = html;
  }

  // 4. Report Filter Dropdown
  const reportSiteSelect = document.getElementById('reportSiteFilterSelect');
  if (reportSiteSelect) {
    let html = `<option value="ALL">All Partner Facilities</option>`;
    sortedSites.forEach(name => {
      const isSelected = name === selectedReportSite ? 'selected' : '';
      html += `<option value="${escapeHtml(name)}" ${isSelected}>${escapeHtml(name)}</option>`;
    });
    reportSiteSelect.innerHTML = html;
  }

  // 5. Tag Group Attendance Modal Facility Filter Dropdown
  populateTagSiteDropdown();
}

function populateTagSiteDropdown(selectedSite = 'ALL') {
  const select = document.getElementById('tagSiteFilterSelect');
  if (!select) return;

  const siteSet = new Set();
  (cachedSites || []).forEach(st => {
    if (st.site_name && st.site_name.trim()) siteSet.add(st.site_name.trim());
  });
  (cachedStudents || []).forEach(s => {
    if (s.site_name && s.site_name.trim()) siteSet.add(s.site_name.trim());
  });
  (cachedLogs || []).forEach(l => {
    if (l.site_name && l.site_name.trim()) siteSet.add(l.site_name.trim());
  });

  const sortedSites = Array.from(siteSet).sort((a, b) => a.localeCompare(b));
  let html = `<option value="ALL">All Facilities</option>`;
  sortedSites.forEach(name => {
    const isSelected = name === selectedSite ? 'selected' : '';
    html += `<option value="${escapeHtml(name)}" ${isSelected}>${escapeHtml(name)}</option>`;
  });
  select.innerHTML = html;
}

// Attendance Logs Filter Handlers
function setLogsDateFilter(filterKey) {
  currentLogsDateFilter = filterKey;
  const customInput = document.getElementById('logsCustomDateInput');
  if (filterKey === 'TODAY') {
    if (customInput) customInput.value = getLocalDateISO();
  } else if (filterKey === 'ALL') {
    if (customInput) customInput.value = '';
    currentLogsCustomDate = '';
    currentLogsStatusFilter = 'ALL';
    currentLogsCourseFilter = 'ALL';
    currentLogsYearLevelFilter = 'ALL';
    currentLogsSiteFilter = 'ALL';

    const statusSel = document.getElementById('logsStatusSelectFilter');
    if (statusSel) statusSel.value = 'ALL';
    const courseSel = document.getElementById('logsCourseSelectFilter');
    if (courseSel) courseSel.value = 'ALL';
    const yearSel = document.getElementById('logsYearLevelSelectFilter');
    if (yearSel) yearSel.value = 'ALL';
    const siteSel = document.getElementById('logsSiteSelectFilter');
    if (siteSel) siteSel.value = 'ALL';
  }
  applyLogsFilters();
}

function handleDateSelectChange(val) {
  setLogsDateFilter(val);
}

function handleCustomDateChange(val) {
  currentLogsCustomDate = val;
  if (!val) {
    currentLogsDateFilter = 'TODAY';
    const customInput = document.getElementById('logsCustomDateInput');
    if (customInput) customInput.value = getLocalDateISO();
  } else {
    currentLogsDateFilter = 'CUSTOM';
  }
  applyLogsFilters();
}

function handleLogsStatusFilterChange(val) {
  currentLogsStatusFilter = val;
  applyLogsFilters();
}

function handleLogsYearLevelFilterChange(val) {
  currentLogsYearLevelFilter = val;
  applyLogsFilters();
}

function handleLogsSiteFilterChange(val) {
  currentLogsYearLevelFilter = val;
  currentLogsSiteFilter = val;
  applyLogsFilters();
}

function handleLogsCourseFilterChange(val) {
  currentLogsCourseFilter = val;
  applyLogsFilters();
}

function filterLogsByCourse(courseCode, updateSelect = true) {
  currentLogsCourseFilter = courseCode || 'ALL';
  const selectEl = document.getElementById('logsCourseSelectFilter');
  if (selectEl && updateSelect) {
    selectEl.value = currentLogsCourseFilter;
  }
  applyLogsFilters();
}

function resetLogsFilters() {
  currentLogsDateFilter = 'TODAY';
  currentLogsCustomDate = '';
  currentLogsStatusFilter = 'ALL';
  currentLogsSiteFilter = 'ALL';
  currentLogsYearLevelFilter = 'ALL';
  currentLogsCourseFilter = 'ALL';

  const dateSelect = document.getElementById('logsDateSelectFilter');
  if (dateSelect) dateSelect.value = 'TODAY';
  const customDate = document.getElementById('logsCustomDateInput');
  if (customDate) { customDate.value = getLocalDateISO(); customDate.style.display = ''; }

  const statusSelect = document.getElementById('logsStatusSelectFilter');
  if (statusSelect) statusSelect.value = 'ALL';

  const yearSelect = document.getElementById('logsYearLevelSelectFilter');
  if (yearSelect) yearSelect.value = 'ALL';

  const siteSelect = document.getElementById('logsSiteSelectFilter');
  if (siteSelect) siteSelect.value = 'ALL';

  const courseSelect = document.getElementById('logsCourseSelectFilter');
  if (courseSelect) courseSelect.value = 'ALL';

  const searchInput = document.getElementById('globalSearch');
  if (searchInput) searchInput.value = '';

  applyLogsFilters('');
}

function updateQuickDatePills() {
  const pills = {
    'TODAY': document.getElementById('btnQuickToday'),
    'YESTERDAY': document.getElementById('btnQuickYesterday'),
    'THIS_WEEK': document.getElementById('btnQuickWeek'),
    'ALL': document.getElementById('btnQuickAll')
  };

  Object.keys(pills).forEach(key => {
    if (pills[key]) {
      if (currentLogsDateFilter === key) {
        pills[key].classList.add('active');
      } else {
        pills[key].classList.remove('active');
      }
    }
  });

  const dateSelect = document.getElementById('logsDateSelectFilter');
  if (dateSelect && dateSelect.value !== currentLogsDateFilter) {
    dateSelect.value = currentLogsDateFilter;
  }
}

function updateFilterStatusBadge(filteredCount, totalCount) {
  const badge = document.getElementById('logsFilterCounterBadge');
  if (!badge) return;

  let label = '';
  if (currentLogsDateFilter === 'TODAY') {
    label = `Showing Today's Logs (${filteredCount})`;
    badge.style.background = '#0284c7';
  } else if (currentLogsDateFilter === 'YESTERDAY') {
    label = `Showing Yesterday's Logs (${filteredCount})`;
    badge.style.background = '#0f766e';
  } else if (currentLogsDateFilter === 'THIS_WEEK') {
    label = `Past 7 Days (${filteredCount})`;
    badge.style.background = '#4338ca';
  } else if (currentLogsDateFilter === 'CUSTOM') {
    label = `Date: ${currentLogsCustomDate} (${filteredCount})`;
    badge.style.background = '#7c3aed';
  } else {
    label = `All Historical Logs (${filteredCount} of ${totalCount})`;
    badge.style.background = 'var(--navy-primary)';
  }

  if (currentLogsStatusFilter !== 'ALL' || currentLogsSiteFilter !== 'ALL' || currentLogsCourseFilter !== 'ALL') {
    label += ` • Filtered`;
  }

  badge.textContent = label;
}

// Unified Multi-Criteria Logs Filter Engine
function applyLogsFilters(searchQuery = null) {
  const globalSearchInput = document.getElementById('globalSearch');
  const q = searchQuery !== null ? searchQuery : (globalSearchInput ? globalSearchInput.value.trim().toLowerCase() : '');

  const todayISO = getLocalDateISO();
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  const yesterdayISO = getLocalDateISO(yesterday);

  const sevenDaysAgo = new Date();
  sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
  sevenDaysAgo.setHours(0, 0, 0, 0);

  let filtered = (cachedLogs || []).filter(l => {
    // 1. Date Filter
    const cleanDate = (l.raw_date || '').substring(0, 10);
    if (currentLogsDateFilter === 'TODAY') {
      if (cleanDate !== todayISO) return false;
    } else if (currentLogsDateFilter === 'YESTERDAY') {
      if (cleanDate !== yesterdayISO) return false;
    } else if (currentLogsDateFilter === 'THIS_WEEK') {
      const logD = new Date(l.raw_date || l.date);
      if (isNaN(logD.getTime()) || logD < sevenDaysAgo) return false;
    } else if (currentLogsDateFilter === 'CUSTOM') {
      if (currentLogsCustomDate && cleanDate !== currentLogsCustomDate) return false;
    }

    // 2. Verification Status Filter
    const hasM = (l.time_in_morning && l.time_in_morning !== '--:--') || (l.time_out_morning && l.time_out_morning !== '--:--');
    const hasA = (l.time_in_afternoon && l.time_in_afternoon !== '--:--') || (l.time_out_afternoon && l.time_out_afternoon !== '--:--');
    const isMPending = hasM && (!l.morning_status || l.morning_status === 'Pending');
    const isAPending = hasA && (!l.afternoon_status || l.afternoon_status === 'Pending');

    if (currentLogsStatusFilter === 'Pending') {
      // Confirmed or fully Rejected records are NEVER pending review
      if (l.status === 'Confirmed' || l.status === 'Rejected') {
        return false;
      }
      const isPending = l.status === 'Pending' || l.status === 'Partial' || isMPending || isAPending;
      if (!isPending) return false;
    } else if (currentLogsStatusFilter === 'Confirmed') {
      if (l.status !== 'Confirmed') return false;
    } else if (currentLogsStatusFilter === 'Rejected') {
      if (l.status !== 'Rejected') return false;
    }

    // 3. Year Level Filter
    if (currentLogsYearLevelFilter !== 'ALL') {
      const fLvl = currentLogsYearLevelFilter.toString().trim().replace(/[^0-9]/g, '');
      const lLvl = (l.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      if (lLvl !== fLvl && !(l.year_level || '').toString().toLowerCase().includes(currentLogsYearLevelFilter.toLowerCase())) {
        return false;
      }
    }

    // Partner Facility Filter (fallback if active)
    if (currentLogsSiteFilter !== 'ALL') {
      if (!l.site_name || l.site_name.trim().toLowerCase() !== currentLogsSiteFilter.trim().toLowerCase()) {
        return false;
      }
    }

    // 4. Academic Program / Course Filter
    if (currentLogsCourseFilter !== 'ALL') {
      if ((l.course_code || '').toUpperCase() !== currentLogsCourseFilter.toUpperCase()) {
        return false;
      }
    }

    // 5. Search Query Filter
    if (q) {
      const match = (l.full_name && l.full_name.toLowerCase().includes(q)) ||
        (l.student_number && l.student_number.toLowerCase().includes(q)) ||
        (l.date && l.date.toLowerCase().includes(q)) ||
        (l.course_code && l.course_code.toLowerCase().includes(q)) ||
        (l.site_name && l.site_name.toLowerCase().includes(q));
      if (!match) return false;
    }

    return true;
  });

  updateQuickDatePills();
  updateFilterStatusBadge(filtered.length, cachedLogs.length);
  renderLogsTable(filtered);
}

function renderLogsTable(logs) {
  const tbody = document.getElementById('logsTableBody');
  if (!tbody) return;

  if (logs.length === 0) {
    let emptyMsg = '';
    if (currentLogsDateFilter === 'TODAY') {
      const todayFormatted = new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
      emptyMsg = `
        <tr>
          <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📅</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 0.35rem;">
              No Attendance Logs Recorded for Today (${todayFormatted})
            </div>
            <p style="font-size: 0.85rem; color: #64748b; max-width: 480px; margin: 0 auto 1.25rem;">
              ${cachedLogs.length > 0 ? `There are ${cachedLogs.length} attendance log record(s) from previous dates (including pending verification snapshots).` : `Students may not have clocked in yet today, or may be assigned to afternoon shifts.`}
            </p>
            ${cachedLogs.length > 0 ? `
              <button type="button" class="btn btn-navy" style="font-size: 0.82rem; padding: 0.45rem 1.2rem; font-weight: 700;" onclick="setLogsDateFilter('ALL')">
                👉 View All Attendance Logs (${cachedLogs.length})
              </button>
            ` : ''}
          </td>
        </tr>`;
    } else {
      emptyMsg = `
        <tr>
          <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
            <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🔍</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 0.35rem;">
              No Logs Match Current Filter Criteria
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
              Try adjusting your Date, Verification Status, Year Level, or Program filters.
            </p>
            <button type="button" class="btn btn-outline" style="font-size: 0.82rem; padding: 0.45rem 1rem;" onclick="resetLogsFilters()">
              ↺ Reset All Filters
            </button>
          </td>
        </tr>`;
    }
    tbody.innerHTML = emptyMsg;
    return;
  }

  tbody.innerHTML = logs.map(l => {
    const hasPhotos = l.photos && l.photos.length > 0;
    const photoBtn = hasPhotos ? `
      <button class="btn btn-navy" style="font-size: 0.78rem; padding: 0.35rem 0.65rem;" onclick='openPhotoModal(${JSON.stringify(l.photos).replace(/'/g, "&apos;")}, ${l.attendance_id})'>
        View (${l.photos.length})
      </button>` : `<span style="color: var(--text-light); font-size: 0.8rem;">No Photo</span>`;
    const courseBadge = getCourseBadgeClass(l.course_code);

    const hasMorning = (l.time_in_morning && l.time_in_morning !== '--:--') || (l.time_out_morning && l.time_out_morning !== '--:--');
    const hasAfternoon = (l.time_in_afternoon && l.time_in_afternoon !== '--:--') || (l.time_out_afternoon && l.time_out_afternoon !== '--:--');
    const isMorningPending = hasMorning && (!l.morning_status || l.morning_status === 'Pending');
    const isAfternoonPending = hasAfternoon && (!l.afternoon_status || l.afternoon_status === 'Pending');

    // Morning shift badge & remarks: remove 'Confirmed' badge if confirmed; display badge only if Rejected or Pending
    let mBadgeHtml = '';
    if (l.morning_status === 'Rejected') {
      mBadgeHtml = `<div style="margin-top: 0.2rem;"><span class="badge badge-danger" style="font-size: 0.68rem;">Rejected</span></div>`;
    } else if (l.morning_status === 'Pending' || (!l.morning_status && hasMorning)) {
      mBadgeHtml = `<div style="margin-top: 0.2rem;"><span class="badge badge-warning" style="font-size: 0.68rem;">Pending</span></div>`;
    }

    const cleanRemarkText = (txt, isMorning = true, hasBoth = false) => {
      let cleaned = (txt || '').replace(/\s*\/\s*in car/gi, '').trim();
      if (hasBoth) {
        if (isMorning && (/morning time-out taken off-site/i.test(cleaned) || /morning time-in taken off-site/i.test(cleaned))) {
          cleaned = 'Morning Shift taken off-site';
        } else if (!isMorning && (/afternoon time-out taken off-site/i.test(cleaned) || /afternoon time-in taken off-site/i.test(cleaned) || /afternoon photo invalid \/ off-site/i.test(cleaned))) {
          cleaned = 'Afternoon Shift taken off-site';
        }
      }
      return cleaned;
    };

    const hasBothMorning = (l.time_in_morning && l.time_in_morning !== '--:--') && (l.time_out_morning && l.time_out_morning !== '--:--');
    const mRemarksHtml = (l.morning_status === 'Rejected' && l.morning_remarks)
      ? `<div style="font-size: 0.72rem; color: #b91c1c; font-style: italic; margin-top: 2px;">${escapeHtml(cleanRemarkText(l.morning_remarks, true, hasBothMorning))}</div>`
      : '';

    // Afternoon shift badge & remarks: remove 'Confirmed' badge if confirmed; display badge only if Rejected or Pending
    let aBadgeHtml = '';
    if (l.afternoon_status === 'Rejected') {
      aBadgeHtml = `<div style="margin-top: 0.2rem;"><span class="badge badge-danger" style="font-size: 0.68rem;">Rejected</span></div>`;
    } else if (l.afternoon_status === 'Pending' || (!l.afternoon_status && hasAfternoon)) {
      aBadgeHtml = `<div style="margin-top: 0.2rem;"><span class="badge badge-warning" style="font-size: 0.68rem;">Pending</span></div>`;
    }

    const hasBothAfternoon = (l.time_in_afternoon && l.time_in_afternoon !== '--:--') && (l.time_out_afternoon && l.time_out_afternoon !== '--:--');
    const aRemarksHtml = (l.afternoon_status === 'Rejected' && l.afternoon_remarks)
      ? `<div style="font-size: 0.72rem; color: #b91c1c; font-style: italic; margin-top: 2px;">${escapeHtml(cleanRemarkText(l.afternoon_remarks, false, hasBothAfternoon))}</div>`
      : '';

    // Overall status badge
    let overallBadge = 'badge-warning';
    if (l.status === 'Confirmed') overallBadge = 'badge-success';
    if (l.status === 'Rejected') overallBadge = 'badge-danger';
    if (l.status === 'Partial') overallBadge = 'badge-warning';

    const isConfirmedRecord = (l.status === 'Confirmed') ||
      (hasMorning && hasAfternoon && l.morning_status === 'Confirmed' && l.afternoon_status === 'Confirmed') ||
      (hasMorning && !hasAfternoon && l.morning_status === 'Confirmed') ||
      (!hasMorning && hasAfternoon && l.afternoon_status === 'Confirmed');

    let evalControls = '';

    if (isConfirmedRecord) {
      evalControls = `
        <div style="font-size: 0.78rem; color: var(--text-muted); text-align: center; font-weight: 600; white-space: nowrap;">
          No Action
        </div>`;
    } else if (l.status === 'Rejected') {
      evalControls = `
        <div style="font-size: 0.78rem; color: #dc2626; text-align: center; font-weight: 600; white-space: nowrap;">
          No Action
        </div>`;
    } else if (hasMorning && !hasAfternoon && isMorningPending) {
      // 1. Morning shift only & pending
      evalControls = `
        <div class="dean-action-cell">
          <button class="dean-btn-tag" style="background-color: #eab308; color: #1e293b;" title="Tag students present in group photo" onclick="openTagGroupModal(${l.attendance_id}, 'morning')">Tag</button>
          <button class="dean-btn-reject" onclick="reviewAttendanceLog(${l.attendance_id}, 'Rejected', 'morning')">Reject</button>
        </div>
      `;
    } else if (hasMorning && hasAfternoon && isMorningPending && isAfternoonPending) {
      // 2. Both shifts, both pending
      evalControls = `
        <div class="dean-action-cell">
          <button class="dean-btn-tag" style="background-color: #eab308; color: #1e293b;" title="Tag students present in group photo" onclick="openTagGroupModal(${l.attendance_id})">Tag</button>
          <button class="dean-btn-reject" title="Reject Attendance Log" onclick="reviewAttendanceLog(${l.attendance_id}, 'Rejected', 'morning')">Reject</button>
        </div>
      `;
    } else if (hasMorning && hasAfternoon && isMorningPending) {
      // 3. Morning pending, afternoon already reviewed
      evalControls = `
        <div class="dean-action-cell">
          <button class="dean-btn-tag" style="background-color: #eab308; color: #1e293b;" title="Tag students present in group photo" onclick="openTagGroupModal(${l.attendance_id}, 'morning')">Tag (M)</button>
          <button class="dean-btn-reject" onclick="reviewAttendanceLog(${l.attendance_id}, 'Rejected', 'morning')">Reject</button>
        </div>
      `;
    } else if (hasMorning && hasAfternoon && isAfternoonPending) {
      // 4. Afternoon pending, morning already reviewed
      evalControls = `
        <div class="dean-action-cell">
          <button class="dean-btn-tag" style="background-color: #eab308; color: #1e293b;" title="Tag students present in group photo" onclick="openTagGroupModal(${l.attendance_id}, 'afternoon')">Tag (A)</button>
          <button class="dean-btn-reject" onclick="reviewAttendanceLog(${l.attendance_id}, 'Rejected', 'afternoon')">Reject</button>
        </div>
      `;
    } else if (!hasMorning && hasAfternoon && isAfternoonPending) {
      // 5. Afternoon shift only & pending
      evalControls = `
        <div class="dean-action-cell">
          <button class="dean-btn-tag" style="background-color: #eab308; color: #1e293b;" title="Tag students present in group photo" onclick="openTagGroupModal(${l.attendance_id}, 'afternoon')">Tag</button>
          <button class="dean-btn-reject" onclick="reviewAttendanceLog(${l.attendance_id}, 'Rejected', 'afternoon')">Reject</button>
        </div>
      `;
    } else {
      evalControls = `
        <div style="font-size: 0.78rem; color: var(--text-muted); text-align: center; font-weight: 500; white-space: nowrap;">
          No Action
        </div>
      `;
    }


    const morningShiftHtml = hasMorning ? `
      <div style="font-size: 0.82rem; font-weight: 600;">${l.time_in_morning} &ndash; ${l.time_out_morning}</div>
      ${mBadgeHtml}
      ${mRemarksHtml}
    ` : `
      <div style="font-size: 0.82rem; color: var(--text-light);">--:-- &ndash; --:--</div>
      <div style="margin-top: 0.2rem;"><span class="badge" style="font-size: 0.68rem; background: #f1f5f9; color: #94a3b8;">Not Logged In</span></div>
    `;

    const afternoonShiftHtml = hasAfternoon ? `
      <div style="font-size: 0.82rem; font-weight: 600;">${l.time_in_afternoon} &ndash; ${l.time_out_afternoon}</div>
      ${aBadgeHtml}
      ${aRemarksHtml}
    ` : `
      <div style="font-size: 0.82rem; color: var(--text-light);">--:-- &ndash; --:--</div>
      <div style="margin-top: 0.2rem;"><span class="badge" style="font-size: 0.68rem; background: #f1f5f9; color: #94a3b8;">Not Logged In</span></div>
    `;

    return `
      <tr>
        <td style="font-weight: 700; color: var(--navy-primary); white-space: nowrap;">${l.date}</td>
        <td>
          <div style="font-weight: 700;">${escapeHtml(l.full_name)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
            <span>${l.student_number}</span> &bull; <span class="badge ${courseBadge}">${l.course_code}</span>
            ${(l.remarks && l.remarks.includes('ID Tap')) ? `<span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-size:0.68rem; padding:0.1rem 0.4rem; font-weight:700;" title="${escapeHtml(l.remarks)}">💳 ID Tap</span>` : (hasPhotos ? `<span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:0.68rem; padding:0.1rem 0.4rem; font-weight:700;">📱 Live Capture</span>` : '')}
          </div>
          <div style="font-size: 0.72rem; color: #64748b;">${escapeHtml(l.site_name || '')}</div>
        </td>
        <td>${morningShiftHtml}</td>
        <td>${afternoonShiftHtml}</td>
        <td><span class="badge ${overallBadge}">${l.status}</span></td>
        <td>${photoBtn}</td>
        <td>${evalControls}</td>
      </tr>
    `;
  }).join('');
}


async function fetchAbsences() {
  try {
    const res = await fetch(API_BASE + 'admin_get_absences.php' + getDeanQueryParam());
    const data = await res.json();
    if (data.status === 'success') {
      cachedAbsences = data.data || [];
      applyAbsenceFilters();
      updateNotifications();
    }
  } catch (err) {
    console.error('Fetch absences error:', err);
  }
}

function applyAbsenceFilters() {
  const statusEl = document.getElementById('absenceStatusSelectFilter');
  const courseEl = document.getElementById('absenceCourseSelectFilter');

  if (statusEl) currentAbsenceStatusFilter = statusEl.value || 'ALL';
  if (courseEl) currentAbsenceCourseFilter = courseEl.value || 'ALL';

  let filtered = cachedAbsences || [];

  if (currentAbsenceStatusFilter !== 'ALL') {
    filtered = filtered.filter(a => (a.status || '').toLowerCase() === currentAbsenceStatusFilter.toLowerCase());
  }

  if (currentAbsenceCourseFilter !== 'ALL') {
    filtered = filtered.filter(a => (a.course_code || '').toUpperCase() === currentAbsenceCourseFilter.toUpperCase());
  }

  renderAbsencesTable(filtered);
}

function renderAbsencesTable(absences) {
  const tbody = document.getElementById('absencesTableBody');
  if (!tbody) return;

  if (absences.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No absence requests submitted.</td></tr>`;
    return;
  }

  tbody.innerHTML = absences.map(a => {
    let badgeClass = 'badge-warning';
    if (a.status === 'Approved') badgeClass = 'badge-success';
    if (a.status === 'Rejected') badgeClass = 'badge-danger';

    const docLink = a.doc_url ? `<a href="${a.doc_url}" target="_blank" style="color: var(--navy-primary); font-weight: 600;">📄 View Document</a>` : '<span style="color: var(--text-light);">None</span>';
    const isPending = a.status === 'Pending';

    return `
      <tr>
        <td style="font-weight: 700; color: var(--navy-primary);">${a.date_absent}</td>
        <td>
          <div style="font-weight: 700;">${escapeHtml(a.full_name)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">${a.student_number} &bull; ${a.course_code}</div>
        </td>
        <td style="max-width: 250px;">${escapeHtml(a.reason)}</td>
        <td>${docLink}</td>
        <td><span class="badge ${badgeClass}">${a.status}</span></td>
        <td style="font-size: 0.8rem; color: var(--text-muted);">${escapeHtml(a.remarks || '--')}</td>
        <td>
          ${isPending ? `
            <button class="btn btn-success" onclick="reviewAbsence(${a.absence_id}, 'Approved')">Approve</button>
            <button class="btn btn-danger" onclick="reviewAbsence(${a.absence_id}, 'Rejected')">Reject</button>
          ` : `<span style="font-size: 0.8rem; color: var(--text-muted);">Evaluated</span>`}
        </td>
      </tr>
    `;
  }).join('');
}

async function reviewAbsence(absenceId, status) {
  const remarks = prompt(`Enter administrative remarks for ${status} decision:`, status === 'Approved' ? 'Approved by Dean of Student Affairs' : 'Medical certificate / supporting document missing');
  if (remarks === null) return;

  try {
    const res = await fetch(API_BASE + 'admin_review_absence.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        absence_id: absenceId,
        status: status,
        remarks: remarks,
        reviewed_by: currentUser ? currentUser.user_id : 1
      })
    });
    const data = await res.json();
    if (data.status === 'success') {
      markNotificationViewed('absence_' + absenceId);
      updateNotifications();
      alert(data.message);
      fetchAbsences();
      fetchOverview();
    } else {
      alert(data.message || 'Operation failed');
    }
  } catch (err) {
    console.error('Review absence error:', err);
    alert('Server error processing request.');
  }
}

// ─── DAILY STUDENT JOURNALS & NARRATIVE REPORTS LOGIC ────────────────────────

async function fetchJournals() {
  try {
    const deanParam = currentUser && currentUser.user_id ? `?dean_id=${currentUser.user_id}` : '';
    const res = await fetch(API_BASE + 'admin_get_journals.php' + deanParam);
    const data = await res.json();

    if (data.status === 'success') {
      cachedJournals = data.data || [];
      const journalDateInput = document.getElementById('journalCustomDateInput');
      if (journalDateInput && !journalDateInput.value) {
        journalDateInput.value = getLocalDateISO();
      }
      populateJournalSiteDropdown();
      populateJournalCourseDropdown();
      applyJournalFilters();
    }
  } catch (err) {
    console.error('Fetch journals error:', err);
  }
}

function populateJournalSiteDropdown() {
  const select = document.getElementById('journalSiteSelectFilter');
  if (!select) return;

  const currentVal = select.value;
  const sitesSet = new Set();
  (cachedSites || []).forEach(s => { if (s.site_name) sitesSet.add(s.site_name); });
  (cachedJournals || []).forEach(j => { if (j.site_name) sitesSet.add(j.site_name); });

  const sortedSites = Array.from(sitesSet).sort();
  let html = `<option value="ALL">All Partner Facilities</option>`;
  sortedSites.forEach(name => {
    html += `<option value="${escapeHtml(name)}">${escapeHtml(name)}</option>`;
  });
  select.innerHTML = html;
  if (currentVal && (currentVal === 'ALL' || sitesSet.has(currentVal))) {
    select.value = currentVal;
  }
}

function populateJournalCourseDropdown() {
  const select = document.getElementById('journalCourseSelectFilter');
  if (!select) return;

  const currentVal = select.value;
  const coursesSet = new Set();
  (cachedCourses || []).forEach(c => { if (c.course_code) coursesSet.add(c.course_code.toUpperCase()); });
  (cachedJournals || []).forEach(j => { if (j.course_code) coursesSet.add(j.course_code.toUpperCase()); });

  const sorted = Array.from(coursesSet).sort();
  let html = `<option value="ALL">All Programs</option>`;
  sorted.forEach(code => {
    const matched = (cachedCourses || []).find(c => (c.course_code || '').toUpperCase() === code);
    const label = matched ? `${matched.course_code} — ${matched.course_name}` : code;
    html += `<option value="${code}">${escapeHtml(label)}</option>`;
  });
  select.innerHTML = html;
  if (currentVal && (currentVal === 'ALL' || coursesSet.has(currentVal.toUpperCase()))) {
    select.value = currentVal;
  }
}

function setJournalDateFilter(type) {
  currentJournalDateFilter = type;
  const customInput = document.getElementById('journalCustomDateInput');
  if (type === 'TODAY') {
    if (customInput) customInput.value = getLocalDateISO();
  } else if (type === 'ALL') {
    if (customInput) customInput.value = '';
    currentJournalCustomDate = '';
  }
  applyJournalFilters();
}

function handleJournalDateSelectChange(val) {
  setJournalDateFilter(val);
}

function handleJournalCustomDateChange(val) {
  currentJournalCustomDate = val;
  if (!val) {
    currentJournalDateFilter = 'TODAY';
    const customInput = document.getElementById('journalCustomDateInput');
    if (customInput) customInput.value = getLocalDateISO();
  } else {
    currentJournalDateFilter = 'CUSTOM';
  }
  applyJournalFilters();
}

function handleJournalStatusFilterChange(val) {
  currentJournalStatusFilter = val;
  applyJournalFilters();
}

function handleJournalYearLevelFilterChange(val) {
  currentJournalYearLevelFilter = val;
  applyJournalFilters();
}

function handleJournalSiteFilterChange(val) {
  currentJournalYearLevelFilter = val;
  currentJournalSiteFilter = val;
  applyJournalFilters();
}

function handleJournalCourseFilterChange(val) {
  currentJournalCourseFilter = val;
  applyJournalFilters();
}

function resetJournalFilters() {
  currentJournalDateFilter = 'TODAY';
  currentJournalCustomDate = '';
  currentJournalStatusFilter = 'ALL';
  currentJournalSiteFilter = 'ALL';
  currentJournalYearLevelFilter = 'ALL';
  currentJournalCourseFilter = 'ALL';

  const customInput = document.getElementById('journalCustomDateInput');
  if (customInput) customInput.value = getLocalDateISO();
  const statusSelect = document.getElementById('journalStatusSelectFilter');
  if (statusSelect) statusSelect.value = 'ALL';
  const yearSelect = document.getElementById('journalYearLevelSelectFilter');
  if (yearSelect) yearSelect.value = 'ALL';
  const siteSelect = document.getElementById('journalSiteSelectFilter');
  if (siteSelect) siteSelect.value = 'ALL';
  const courseSelect = document.getElementById('journalCourseSelectFilter');
  if (courseSelect) courseSelect.value = 'ALL';
  const globalSearch = document.getElementById('globalSearch');
  if (globalSearch && currentTab === 'journals') globalSearch.value = '';

  applyJournalFilters();
}

function updateJournalQuickDatePills() {
  const pills = {
    'TODAY': document.getElementById('btnJournalQuickToday'),
    'YESTERDAY': document.getElementById('btnJournalQuickYesterday'),
    'THIS_WEEK': document.getElementById('btnJournalQuickWeek'),
    'ALL': document.getElementById('btnJournalQuickAll'),
  };

  Object.entries(pills).forEach(([key, btn]) => {
    if (!btn) return;
    if (currentJournalDateFilter === key) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
}

function updateJournalFilterStatusBadge(filteredCount, totalCount) {
  const badge = document.getElementById('journalsFilterCounterBadge');
  if (!badge) return;

  let label = '';
  if (currentJournalDateFilter === 'TODAY') {
    label = `Today's Submissions (${filteredCount})`;
    badge.style.background = '#0284c7';
  } else if (currentJournalDateFilter === 'YESTERDAY') {
    label = `Yesterday's Submissions (${filteredCount})`;
    badge.style.background = '#d97706';
  } else if (currentJournalDateFilter === 'THIS_WEEK') {
    label = `Past 7 Days (${filteredCount})`;
    badge.style.background = '#4338ca';
  } else if (currentJournalDateFilter === 'CUSTOM') {
    label = `Date: ${currentJournalCustomDate} (${filteredCount})`;
    badge.style.background = '#7c3aed';
  } else {
    label = `All Historical Entries (${filteredCount} of ${totalCount})`;
    badge.style.background = 'var(--navy-primary)';
  }

  if (currentJournalStatusFilter !== 'ALL' || currentJournalSiteFilter !== 'ALL' || currentJournalCourseFilter !== 'ALL') {
    label += ` • Filtered`;
  }

  badge.textContent = label;
}

function applyJournalFilters(searchQuery = null) {
  const globalSearchInput = document.getElementById('globalSearch');
  const q = searchQuery !== null ? searchQuery : (globalSearchInput ? globalSearchInput.value.trim().toLowerCase() : '');

  const todayISO = getLocalDateISO();
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  const yesterdayISO = getLocalDateISO(yesterday);

  const sevenDaysAgo = new Date();
  sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
  sevenDaysAgo.setHours(0, 0, 0, 0);

  let filtered = (cachedJournals || []).filter(j => {
    // 1. Date Filter
    const cleanDate = (j.raw_date || '').substring(0, 10);
    if (currentJournalDateFilter === 'TODAY') {
      if (cleanDate !== todayISO) return false;
    } else if (currentJournalDateFilter === 'YESTERDAY') {
      if (cleanDate !== yesterdayISO) return false;
    } else if (currentJournalDateFilter === 'THIS_WEEK') {
      const entryD = new Date(j.raw_date || j.date);
      if (isNaN(entryD.getTime()) || entryD < sevenDaysAgo) return false;
    } else if (currentJournalDateFilter === 'CUSTOM') {
      if (currentJournalCustomDate && cleanDate !== currentJournalCustomDate) return false;
    }

    // 2. Status Filter
    if (currentJournalStatusFilter !== 'ALL') {
      if ((j.dean_status || 'Pending') !== currentJournalStatusFilter) return false;
    }

    // 3. Year Level Filter
    if (currentJournalYearLevelFilter !== 'ALL') {
      const fLvl = currentJournalYearLevelFilter.toString().trim().replace(/[^0-9]/g, '');
      const jLvl = (j.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      if (jLvl !== fLvl && !(j.year_level || '').toString().toLowerCase().includes(currentJournalYearLevelFilter.toLowerCase())) return false;
    }

    // Site Filter (fallback if set)
    if (currentJournalSiteFilter !== 'ALL') {
      if ((j.site_name || '').toLowerCase() !== currentJournalSiteFilter.toLowerCase()) return false;
    }

    // 4. Course Filter
    if (currentJournalCourseFilter !== 'ALL') {
      if ((j.course_code || '').toUpperCase() !== currentJournalCourseFilter.toUpperCase()) return false;
    }

    // 5. Global Search Text Filter
    if (q) {
      const matchName = (j.full_name || '').toLowerCase().includes(q);
      const matchNumber = (j.student_number || '').toLowerCase().includes(q);
      const matchTasks = (j.tasks_completed || '').toLowerCase().includes(q);
      const matchLearnings = (j.learnings_reflection || '').toLowerCase().includes(q);
      const matchSite = (j.site_name || '').toLowerCase().includes(q);
      if (!matchName && !matchNumber && !matchTasks && !matchLearnings && !matchSite) return false;
    }

    return true;
  });

  updateJournalQuickDatePills();
  updateJournalFilterStatusBadge(filtered.length, cachedJournals.length);
  renderJournalsTable(filtered);
}

function renderJournalsTable(journals) {
  const tbody = document.getElementById('journalsTableBody');
  if (!tbody) return;

  if (journals.length === 0) {
    let emptyMsg = '';
    if (currentJournalDateFilter === 'TODAY') {
      const todayFormatted = new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
      emptyMsg = `
        <tr>
          <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 0.35rem;">
              No Daily Journals Submitted for Today (${todayFormatted})
            </div>
            <p style="font-size: 0.85rem; color: #64748b; max-width: 450px; margin: 0 auto;">
              Students typically submit their narrative reports at the end of their shift.
            </p>
          </td>
        </tr>`;
    } else {
      emptyMsg = `
        <tr>
          <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
            <div style="font-size: 1.05rem; font-weight: 700; color: var(--navy-primary); margin-bottom: 0.35rem;">
              No Daily Journals Match Filter Criteria
            </div>
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
              Try adjusting your Date, Review Status, Year Level, or Program filters.
            </p>
            <button type="button" class="btn btn-outline" style="font-size: 0.82rem; padding: 0.45rem 1rem;" onclick="resetJournalFilters()">
              Reset All Filters
            </button>
          </td>
        </tr>`;
    }
    tbody.innerHTML = emptyMsg;
    return;
  }

  tbody.innerHTML = journals.map(j => {
    let statusBadge = `<span class="journal-badge-pending">Pending Review</span>`;
    if (j.dean_status === 'Reviewed') {
      statusBadge = `<span class="journal-badge-reviewed">Reviewed</span>`;
    } else if (j.dean_status === 'Commended') {
      statusBadge = `<span class="journal-badge-commended">Commended</span>`;
    }

    const courseBadge = getCourseBadgeClass(j.course_code);
    const feedbackHtml = j.dean_feedback ? `
      <div class="journal-feedback-quote">
        <strong>Dean:</strong> "${escapeHtml(j.dean_feedback)}"
      </div>` : `<span style="font-size: 0.78rem; color: #94a3b8; font-style: italic;">No feedback entered</span>`;

    return `
      <tr>
        <td style="font-weight: 700; color: var(--navy-primary); white-space: nowrap;">
          ${escapeHtml(j.date)}
        </td>
        <td>
          <div style="font-weight: 700; color: var(--text-main);">${escapeHtml(j.full_name)}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
            ${escapeHtml(j.student_number)} • <span class="badge ${courseBadge}" style="font-size: 0.68rem;">${escapeHtml(j.course_code)}</span>
          </div>
        </td>
        <td>
          <div style="font-weight: 600; font-size: 0.82rem; color: var(--navy-primary);">${escapeHtml(j.site_name)}</div>
          <div style="font-size: 0.72rem; color: #64748b; margin-top: 2px;">
            AM: ${j.time_in_morning} - ${j.time_out_morning} | PM: ${j.time_in_afternoon} - ${j.time_out_afternoon}
          </div>
        </td>
        <td>
          <div>${statusBadge}</div>
          ${feedbackHtml}
        </td>
        <td>
          <div style="display: flex; gap: 0.35rem; align-items: center;">
            <button type="button" class="btn btn-navy" style="font-size: 0.72rem; padding: 0.3rem 0.65rem;" onclick="openJournalModal(${j.journal_id})">
              Read & Feedback
            </button>
            <button type="button" class="btn btn-danger" style="font-size: 0.72rem; padding: 0.3rem 0.55rem;" title="Permanently Delete Journal from Database" onclick="deleteJournal(${j.journal_id})">
              Delete
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function openJournalModal(journalId) {
  const j = (cachedJournals || []).find(item => item.journal_id === journalId);
  if (!j) return;

  currentActiveJournalId = journalId;

  const modal = document.getElementById('journalModal');
  const title = document.getElementById('journalModalTitle');
  const subtitle = document.getElementById('journalModalSubtitle');
  const content = document.getElementById('journalModalContent');

  if (!modal || !content) return;

  title.textContent = `Daily Journal: ${j.full_name}`;
  subtitle.textContent = `${j.date} • ${j.student_number} • ${j.course_code} • ${j.site_name}`;

  content.innerHTML = `
    <!-- Student & Shift Information Banner -->
    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-canvas); border: 1px solid var(--border-light); padding: 0.75rem 1rem; border-radius: var(--radius-sm); flex-wrap: wrap; gap: 0.5rem;">
      <div>
        <div style="font-size: 0.88rem; font-weight: 800; color: var(--navy-primary);">${escapeHtml(j.full_name)} (${escapeHtml(j.student_number)})</div>
        <div style="font-size: 0.78rem; color: var(--text-muted);">${escapeHtml(j.course_name)} &bull; ${escapeHtml(j.site_name)}</div>
      </div>
      <div style="text-align: right; font-size: 0.75rem; color: #475569;">
        <div><strong>Entry Date:</strong> ${escapeHtml(j.date)}</div>
        <div><strong>Attendance:</strong> AM: ${j.time_in_morning} - ${j.time_out_morning} | PM: ${j.time_in_afternoon} - ${j.time_out_afternoon}</div>
      </div>
    </div>

    <!-- Key Learnings & Reflections -->
    <div class="journal-section-card">
      <div class="journal-section-title">
        Key Learnings &amp; Reflections
      </div>
      <div class="journal-section-text">${escapeHtml(j.learnings_reflection || j.tasks_completed || 'No specific reflections noted for this shift.')}</div>
    </div>

    <!-- Legacy Tasks (if distinct from learnings) -->
    ${(j.tasks_completed && j.tasks_completed !== j.learnings_reflection) ? `
      <div class="journal-section-card">
        <div class="journal-section-title">
          Daily Activities &amp; Tasks Accomplished
        </div>
        <div class="journal-section-text">${escapeHtml(j.tasks_completed)}</div>
      </div>
    ` : ''}

    <!-- Challenges Encountered (if any) -->
    ${j.challenges_encountered ? `
      <div class="journal-section-card">
        <div class="journal-section-title">
          Challenges Encountered &amp; Resolutions
        </div>
        <div class="journal-section-text">${escapeHtml(j.challenges_encountered)}</div>
      </div>
    ` : ''}

    <!-- Section 4: Dean Evaluation & Feedback Input -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radius-sm); padding: 1rem;">
      <div style="font-size: 0.84rem; font-weight: 800; color: #1e3a8a; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.35rem;">
        Dean Evaluation &amp; Feedback Remarks
      </div>

      <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 0.75rem; margin-bottom: 0.65rem;">
        <div>
          <label style="display: block; font-size: 0.74rem; font-weight: 700; color: #1e3a8a; margin-bottom: 0.25rem;">Evaluation Status:</label>
          <select id="modalJournalStatusSelect" class="form-control" style="font-size: 0.82rem; height: 36px; border-color: #93c5fd;">
            <option value="Reviewed" ${j.dean_status === 'Reviewed' ? 'selected' : ''}>✓ Reviewed &amp; Noted</option>
            <option value="Commended" ${j.dean_status === 'Commended' ? 'selected' : ''}>⭐ Commended (Exemplary)</option>
            <option value="Pending" ${j.dean_status === 'Pending' ? 'selected' : ''}>⏳ Pending</option>
          </select>
        </div>
        <div>
          <label style="display: block; font-size: 0.74rem; font-weight: 700; color: #1e3a8a; margin-bottom: 0.25rem;">Last Evaluation Timestamp:</label>
          <div style="font-size: 0.78rem; color: #475569; padding-top: 0.45rem;">
            ${j.reviewed_at ? `Evaluated on ${j.reviewed_at}` : 'Not evaluated yet'}
          </div>
        </div>
      </div>

      <div>
        <label style="display: block; font-size: 0.74rem; font-weight: 700; color: #1e3a8a; margin-bottom: 0.25rem;">Dean Feedback / Guidance for Student:</label>
        <textarea id="modalJournalFeedbackText" class="form-control" rows="3" placeholder="Type constructive feedback, guidance, or words of encouragement for this student..." style="font-size: 0.82rem; border-color: #93c5fd; resize: vertical;">${escapeHtml(j.dean_feedback || '')}</textarea>
        <small style="display: block; color: #64748b; font-size: 0.72rem; margin-top: 0.25rem;">
          💡 This feedback is immediately visible to the student inside their mobile app.
        </small>
      </div>
    </div>
  `;

  modal.classList.add('active');
}

function closeJournalModal() {
  const modal = document.getElementById('journalModal');
  if (modal) modal.classList.remove('active');
  currentActiveJournalId = null;
}

async function saveJournalEvaluation() {
  if (!currentActiveJournalId) return;

  const statusSelect = document.getElementById('modalJournalStatusSelect');
  const feedbackText = document.getElementById('modalJournalFeedbackText');

  const deanStatus = statusSelect ? statusSelect.value : 'Reviewed';
  const deanFeedback = feedbackText ? feedbackText.value.trim() : '';

  try {
    const res = await fetch(API_BASE + 'admin_review_journal.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        journal_id: currentActiveJournalId,
        dean_status: deanStatus,
        dean_feedback: deanFeedback
      })
    });

    const data = await res.json();
    if (data.status === 'success') {
      alert('✓ Journal evaluation and feedback saved successfully.');
      closeJournalModal();
      fetchJournals();
    } else {
      alert(data.message || 'Failed to save evaluation.');
    }
  } catch (err) {
    console.error('Save journal evaluation error:', err);
    alert('Server error saving evaluation.');
  }
}

async function deleteJournal(journalId) {
  if (!confirm('⚠️ PERMANENT DATABASE DELETION:\nAre you sure you want to delete this daily student journal from the database? This cannot be undone.')) {
    return;
  }

  try {
    const res = await fetch(API_BASE + 'admin_delete_journal.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete_one',
        journal_id: journalId,
        dean_id: currentUser ? currentUser.user_id : 0
      })
    });

    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      if (currentActiveJournalId === journalId) {
        closeJournalModal();
      }
      cachedJournals = (cachedJournals || []).filter(j => j.journal_id !== journalId);
      applyJournalFilters();
    } else {
      alert(data.message || 'Failed to delete journal entry.');
    }
  } catch (err) {
    console.error('Delete journal error:', err);
    alert('Server error processing deletion.');
  }
}

function handleModalDeleteJournal() {
  if (currentActiveJournalId) {
    deleteJournal(currentActiveJournalId);
  }
}

async function deleteAllFilteredJournals() {
  const currentCount = document.querySelectorAll('#journalsTableBody tr').length;
  if (currentCount === 0 || cachedJournals.length === 0) {
    alert('No journal entries currently available to delete.');
    return;
  }

  let promptMsg = `⚠️ PERMANENT BULK DATABASE DELETION:\n\nAre you sure you want to permanently delete all journal entries from the database?`;
  if (currentJournalDateFilter === 'TODAY') {
    promptMsg = `⚠️ PERMANENT DELETION: Delete all of TODAY's journal entries from the database?`;
  } else if (currentJournalDateFilter === 'YESTERDAY') {
    promptMsg = `⚠️ PERMANENT DELETION: Delete all of YESTERDAY's journal entries from the database?`;
  }

  if (!confirm(promptMsg)) return;

  try {
    const bodyPayload = {
      action: 'delete_all',
      dean_id: currentUser ? currentUser.user_id : 0
    };

    if (currentJournalDateFilter === 'TODAY') {
      bodyPayload.date = getLocalDateISO();
    } else if (currentJournalDateFilter === 'YESTERDAY') {
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      bodyPayload.date = getLocalDateISO(yesterday);
    } else if (currentJournalDateFilter === 'CUSTOM' && currentJournalCustomDate) {
      bodyPayload.date = currentJournalCustomDate;
    }

    const res = await fetch(API_BASE + 'admin_delete_journal.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(bodyPayload)
    });

    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      fetchJournals();
    } else {
      alert(data.message || 'Bulk deletion failed.');
    }
  } catch (err) {
    console.error('Delete all journals error:', err);
    alert('Server error executing bulk deletion.');
  }
}

async function fetchSites() {
  try {
    const res = await fetch(API_BASE + 'admin_sites.php' + getDeanQueryParam());
    const data = await res.json();
    if (data.status === 'success') {
      cachedSites = data.data || [];
      renderSitesTable(cachedSites);
      populateLogsSiteDropdown();
    }
  } catch (err) {
    console.error('Fetch sites error:', err);
  }
}

function renderSitesTable(sites) {
  const tbody = document.getElementById('sitesTableBody');
  if (!tbody) return;

  if (sites.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No training partner facilities registered.</td></tr>`;
    return;
  }

  tbody.innerHTML = sites.map(st => `
    <tr>
      <td style="font-weight: 700; color: var(--navy-primary);">${st.site_code}</td>
      <td style="font-weight: 700;">${escapeHtml(st.site_name)}</td>
      <td>${escapeHtml(st.location)}</td>
      <td><span class="badge badge-success">${st.assigned_interns} Active Students</span></td>
      <td>
        <button class="btn btn-outline" onclick='openEditSiteModal(${JSON.stringify(st).replace(/'/g, "&apos;")})'>Edit Partner</button>
        <button class="btn btn-danger" style="margin-left: 0.35rem; padding: 0.4rem 0.75rem; font-size: 0.8rem;" onclick="deletePartnerSite(${st.site_id}, '${escapeHtml(st.site_name).replace(/'/g, "\\'")}', ${st.assigned_interns})">Delete</button>
      </td>
    </tr>
  `).join('');
}

let activeEditingSite = null;

function openAddSiteModal() {
  activeEditingSite = null;
  document.getElementById('siteModalTitle').textContent = 'Add Partner Training Facility';
  document.getElementById('siteIdInput').value = '';
  document.getElementById('siteCodeInput').value = '';
  document.getElementById('siteNameInput').value = '';
  document.getElementById('siteLocationInput').value = '';
  const delBtn = document.getElementById('deleteSiteModalBtn');
  if (delBtn) delBtn.style.display = 'none';
  document.getElementById('siteModal').classList.add('active');
}

function openEditSiteModal(site) {
  activeEditingSite = site;
  document.getElementById('siteModalTitle').textContent = 'Edit Partner Training Facility';
  document.getElementById('siteIdInput').value = site.site_id;
  document.getElementById('siteCodeInput').value = site.site_code;
  document.getElementById('siteNameInput').value = site.site_name;
  document.getElementById('siteLocationInput').value = site.location;
  const delBtn = document.getElementById('deleteSiteModalBtn');
  if (delBtn) delBtn.style.display = 'inline-block';
  document.getElementById('siteModal').classList.add('active');
}

function closeSiteModal() {
  document.getElementById('siteModal').classList.remove('active');
}

async function handleModalDeleteSite() {
  if (!activeEditingSite) return;
  closeSiteModal();
  await deletePartnerSite(activeEditingSite.site_id, activeEditingSite.site_name, activeEditingSite.assigned_interns);
}

async function deletePartnerSite(siteId, siteName, assignedInterns) {
  if (assignedInterns > 0) {
    alert(`Cannot delete '${siteName}' because it currently has ${assignedInterns} active assigned student(s). Please reassign those students to another facility first.`);
    return;
  }

  if (!confirm(`Are you sure you want to permanently remove '${siteName}' from partner facilities?`)) {
    return;
  }

  try {
    const res = await fetch(API_BASE + 'admin_sites.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', site_id: siteId })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      fetchSites();
    } else {
      alert(data.message || 'Failed to delete partner facility.');
    }
  } catch (err) {
    console.error('Delete site error:', err);
  }
}

async function savePartnerSite(e) {
  e.preventDefault();
  const site_id = document.getElementById('siteIdInput').value;
  const site_code = document.getElementById('siteCodeInput').value.trim();
  const site_name = document.getElementById('siteNameInput').value.trim();
  const location = document.getElementById('siteLocationInput').value.trim();

  try {
    const res = await fetch(API_BASE + 'admin_sites.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ site_id, site_code, site_name, location })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      closeSiteModal();
      fetchSites();
    } else {
      alert(data.message || 'Failed to save site.');
    }
  } catch (err) {
    console.error('Save site error:', err);
  }
}


async function fetchReports() {
  await fetchStudents();
  renderComplianceSummary();
}

function filterReportByCourse(courseCode) {
  selectedReportCourse = courseCode || 'ALL';
  renderComplianceSummary();
}

function filterReportByStatus(status) {
  selectedReportStatus = status || 'ALL';
  renderComplianceSummary();
}

function filterReportByYearLevel(yearLevel) {
  selectedReportYearLevel = yearLevel || 'ALL';
  renderComplianceSummary();
}

function filterReportBySite(siteName) {
  selectedReportSite = siteName || 'ALL';
  renderComplianceSummary();
}

function getReportSiteOptionsHtml() {
  const siteSet = new Set();
  (cachedSites || []).forEach(st => {
    if (st.site_name && st.site_name.trim()) siteSet.add(st.site_name.trim());
  });
  (cachedStudents || []).forEach(s => {
    if (s.site_name && s.site_name.trim()) siteSet.add(s.site_name.trim());
  });
  const sorted = Array.from(siteSet).sort((a, b) => a.localeCompare(b));
  return sorted.map(name => {
    const isSel = name === selectedReportSite ? 'selected' : '';
    return `<option value="${escapeHtml(name)}" ${isSel}>${escapeHtml(name)}</option>`;
  }).join('');
}

function getReportStudents() {
  let filtered = cachedStudents || [];

  if (selectedReportCourse !== 'ALL') {
    filtered = filtered.filter(s => (s.course_code || '').toUpperCase() === selectedReportCourse.toUpperCase());
  }

  if (selectedReportStatus !== 'ALL') {
    if (selectedReportStatus === 'REGULAR') {
      filtered = filtered.filter(s => (s.total_absences || 0) === 0);
    } else if (selectedReportStatus === 'IRREGULAR') {
      filtered = filtered.filter(s => (s.total_absences || 0) > 0);
    } else {
      filtered = filtered.filter(s => (s.status || '').toLowerCase() === selectedReportStatus.toLowerCase());
    }
  }

  if (selectedReportYearLevel !== 'ALL') {
    filtered = filtered.filter(s => {
      const sLvl = (s.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      const fLvl = selectedReportYearLevel.toString().trim().replace(/[^0-9]/g, '');
      return sLvl === fLvl || (s.year_level || '').toString().toLowerCase().includes(selectedReportYearLevel.toLowerCase());
    });
  }

  if (selectedReportSite !== 'ALL') {
    filtered = filtered.filter(s => (s.site_name && s.site_name.trim().toLowerCase() === selectedReportSite.trim().toLowerCase()));
  }

  return filtered;
}

function renderComplianceSummary() {
  const container = document.getElementById('reportsContainer');
  if (!container) return;

  const targetStudents = getReportStudents();
  const total = targetStudents.length;
  const regularCount = targetStudents.filter(s => (s.total_absences || 0) === 0).length;
  const irregularCount = targetStudents.filter(s => (s.total_absences || 0) > 0).length;
  const regularPercent = total > 0 ? Math.round((regularCount / total) * 100) : 0;

  // Build program options dynamically from cached courses
  const programOptionsHtml = `<option value="ALL" ${selectedReportCourse === 'ALL' ? 'selected' : ''}>All Academic Programs</option>` +
    (cachedCourses || []).map(c => `
      <option value="${escapeHtml(c.course_code)}" ${selectedReportCourse === c.course_code ? 'selected' : ''}>
        ${escapeHtml(c.course_code)} - ${escapeHtml(c.course_name)}
      </option>
    `).join('');

  container.innerHTML = `
    <!-- Multi-Criteria Report Filters Header -->
    <div class="card" style="padding: 1.15rem 1.4rem; margin-bottom: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
      <div class="report-filter-grid">
        <!-- Program -->
        <div>
          <label for="reportCourseFilterSelect" style="display: block; font-size: 0.73rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem;">
            Academic Program
          </label>
          <select id="reportCourseFilterSelect" class="form-control" style="width: 100%; height: 38px; font-size: 0.83rem; font-weight: 600; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem;" onchange="filterReportByCourse(this.value)">
            ${programOptionsHtml}
          </select>
        </div>

        <!-- Attendance Standing Filter -->
        <div>
          <label for="reportStatusFilterSelect" style="display: block; font-size: 0.73rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem;">
            Attendance Standing
          </label>
          <select id="reportStatusFilterSelect" class="form-control" style="width: 100%; height: 38px; font-size: 0.83rem; font-weight: 600; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem;" onchange="filterReportByStatus(this.value)">
            <option value="ALL" ${selectedReportStatus === 'ALL' ? 'selected' : ''}>All Standings</option>
            <option value="REGULAR" ${selectedReportStatus === 'REGULAR' ? 'selected' : ''}>Regular Attendees (0 Absences)</option>
            <option value="IRREGULAR" ${selectedReportStatus === 'IRREGULAR' ? 'selected' : ''}>Irregular Attendees (1+ Absences)</option>
          </select>
        </div>

        <!-- Year Level -->
        <div>
          <label for="reportYearLevelFilterSelect" style="display: block; font-size: 0.73rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem;">
            Year Level
          </label>
          <select id="reportYearLevelFilterSelect" class="form-control" style="width: 100%; height: 38px; font-size: 0.83rem; font-weight: 600; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 0.75rem;" onchange="filterReportByYearLevel(this.value)">
            <option value="ALL" ${selectedReportYearLevel === 'ALL' ? 'selected' : ''}>All Year Levels</option>
            <option value="1" ${selectedReportYearLevel === '1' ? 'selected' : ''}>1st Year</option>
            <option value="2" ${selectedReportYearLevel === '2' ? 'selected' : ''}>2nd Year</option>
            <option value="3" ${selectedReportYearLevel === '3' ? 'selected' : ''}>3rd Year</option>
            <option value="4" ${selectedReportYearLevel === '4' ? 'selected' : ''}>4th Year</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="report-filter-actions">
          <button type="button" class="btn btn-outline" onclick="exportCSVReport()" style="height: 38px; padding: 0 1rem; font-size: 0.82rem; font-weight: 700; border-radius: 6px; white-space: nowrap; color: var(--navy-primary); border-color: #cbd5e1;">
            Export CSV
          </button>
          <button type="button" class="btn btn-danger" onclick="openBatchResetModal()" style="height: 38px; padding: 0 1rem; font-size: 0.82rem; font-weight: 700; border-radius: 6px; white-space: nowrap;">
            Reset Storage
          </button>
          <button type="button" class="btn btn-navy" onclick="generatePDFReport()" style="height: 38px; padding: 0 1.25rem; font-size: 0.82rem; font-weight: 700; border-radius: 6px; white-space: nowrap; box-shadow: 0 2px 4px rgba(0, 45, 86, 0.15);">
            Generate PDF Report
          </button>
        </div>
      </div>
    </div>

    <!-- Executive KPI Grid -->
    <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
      <div class="kpi-card">
        <div class="kpi-title">Total Enrolled Students</div>
        <div class="kpi-value-row">
          <div class="kpi-value">${total}</div>
          <span class="kpi-badge gold">${selectedReportCourse === 'ALL' ? 'All Programs' : selectedReportCourse}</span>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-title">Regular Attendees</div>
        <div class="kpi-value-row">
          <div class="kpi-value">${regularCount}</div>
          <span class="kpi-badge green">${regularPercent}% No Absences</span>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-title">With Absences (Irregular)</div>
        <div class="kpi-value-row">
          <div class="kpi-value">${irregularCount}</div>
          <span class="kpi-badge ${irregularCount > 0 ? 'gold' : 'green'}">${irregularCount > 0 ? irregularCount + ' Flagged' : 'None'}</span>
        </div>
      </div>
    </div>

    <!-- Report Table Card -->
    <div class="card mt-3">
      <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
        <h3>Institutional Attendance &amp; Standing Summary Report ${selectedReportCourse !== 'ALL' ? `(${selectedReportCourse})` : ''}</h3>
        <span class="badge badge-navy" style="font-size: 0.8rem;">${total} Student Record(s)</span>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Student #</th>
              <th>Course</th>
              <th>Year Level</th>
              <th>Attendance Standing</th>
            </tr>
          </thead>
          <tbody>
            ${targetStudents.length === 0 ? `
              <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No students found matching current criteria.</td></tr>
            ` : targetStudents.map(s => {
              const courseBadge = getCourseBadgeClass(s.course_code);
              const absences = s.total_absences || 0;

              let attendanceBadge = '';
              if (absences === 0) {
                attendanceBadge = '<span class="badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; font-weight:700;">Regular Attendee (0 Absences)</span>';
              } else {
                attendanceBadge = `<span class="badge" style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; font-weight:700;">Irregular Attendee (${absences} Absence${absences > 1 ? 's' : ''})</span>`;
              }

              return `
                <tr>
                  <td style="font-weight:700;">${escapeHtml(s.full_name)}</td>
                  <td style="font-family:monospace; font-weight:600; color:#475569;">${escapeHtml(s.student_number)}</td>
                  <td><span class="badge ${courseBadge}">${escapeHtml(s.course_code)}</span></td>
                  <td style="font-weight: 600;">${formatYearLevelDisplay(s.year_level)}</td>
                  <td>${attendanceBadge}</td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    </div>
  `;
}

function getLogoDataUrl() {
  try {
    const sidebarLogo = document.querySelector('.sidebar-logo img') || document.querySelector('.auth-hero-logo img');
    if (sidebarLogo && sidebarLogo.complete && sidebarLogo.naturalWidth > 0) {
      const c = document.createElement('canvas');
      c.width = sidebarLogo.naturalWidth;
      c.height = sidebarLogo.naturalHeight;
      const ctx = c.getContext('2d');
      ctx.drawImage(sidebarLogo, 0, 0);
      return c.toDataURL('image/png');
    }
  } catch (e) {
    console.warn('Could not extract logo data URL:', e);
  }
  return 'assets/images/cit-logo.png';
}

async function generatePDFReport() {
  const targetStudents = getReportStudents();
  if (targetStudents.length === 0) return alert('No student records found to generate PDF report.');

  const total = targetStudents.length;
  const rfidCount = targetStudents.filter(s => s.rfid_uid && s.rfid_uid.trim()).length;
  const activeCount = targetStudents.filter(s => (s.total_days || 0) > 0).length;

  let programTitle = 'All Academic Programs';
  if (selectedReportCourse !== 'ALL') {
    const cObj = (cachedCourses || []).find(c => c.course_code === selectedReportCourse);
    programTitle = cObj ? `${cObj.course_code} - ${cObj.course_name}` : selectedReportCourse;
  }

  const deanName = currentUser ? currentUser.full_name : 'Dean Admin';
  const deanEmail = currentUser ? currentUser.email : 'dean@cit.edu.ph';
  const dateStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  const dateTag = new Date().toISOString().slice(0, 10);
  const pdfFilename = `CIT_Attendance_Standing_Report_${selectedReportCourse}_${dateTag}.pdf`;
  const logoUrl = getLogoDataUrl();

  const offscreenContainer = document.createElement('div');
  offscreenContainer.style.position = 'fixed';
  offscreenContainer.style.top = '0';
  offscreenContainer.style.left = '0';
  offscreenContainer.style.width = '695px';
  offscreenContainer.style.opacity = '0';
  offscreenContainer.style.pointerEvents = 'none';
  offscreenContainer.style.zIndex = '-9999';
  offscreenContainer.style.overflow = 'visible';

  const reportWrapper = document.createElement('div');
  reportWrapper.style.boxSizing = 'border-box';
  reportWrapper.style.width = '695px';
  reportWrapper.style.padding = '14px 16px';
  reportWrapper.style.backgroundColor = '#ffffff';
  reportWrapper.style.color = '#1e293b';
  reportWrapper.style.fontFamily = "'Helvetica Neue', Helvetica, Arial, sans-serif";
  reportWrapper.style.fontSize = '10px';
  reportWrapper.style.lineHeight = '1.35';

  reportWrapper.innerHTML = `
    <div style="display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 2px solid #002d56; padding-bottom: 8px; margin-bottom: 10px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <img src="${logoUrl}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: contain; flex-shrink: 0;" alt="CIT Logo" onerror="this.style.display='none';">
        <div>
          <div style="font-size: 14px; font-weight: 800; color: #002d56; text-transform: uppercase; letter-spacing: 0.3px; margin: 0;">College of Information Technology</div>
          <div style="font-size: 9.5px; font-weight: 600; color: #475569; margin-top: 2px;">Office of Student Affairs &amp; Academic Records</div>
          <div style="font-size: 10.5px; font-weight: 700; color: #d97706; text-transform: uppercase; margin-top: 2px;">Institutional Student Attendance &amp; Standing Report</div>
        </div>
      </div>
      <div style="text-align: right; font-size: 9px; color: #64748b; flex-shrink: 0; padding-left: 10px;">
        <strong style="color: #1e293b;">Date Generated:</strong><br>
        <span style="white-space: nowrap;">${dateStr}</span>
      </div>
    </div>

    <div style="display: flex; gap: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 5px; margin-bottom: 14px;">
      <div style="flex: 1; font-size: 9.5px;">
        <div><strong style="color: #002d56;">Target Program Scope:</strong> ${escapeHtml(programTitle)}</div>
        <div style="margin-top: 2px;"><strong style="color: #002d56;">Student Roster:</strong> ${total} Enrolled | ${targetStudents.filter(s => (s.total_absences || 0) === 0).length} Regular Attendees | ${targetStudents.filter(s => (s.total_absences || 0) > 0).length} Irregular Attendees</div>
      </div>
      <div style="flex: 1; font-size: 9.5px;">
        <div><strong style="color: #002d56;">Issued By:</strong> ${escapeHtml(deanName)} (${escapeHtml(deanEmail)})</div>
        <div style="margin-top: 2px;"><strong style="color: #002d56;">Report Type:</strong> Official Institutional Attendance &amp; Standing Record</div>
      </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 8.5px;">
      <thead>
        <tr style="background-color: #002d56; color: #ffffff;">
          <th style="width: 32%; padding: 6px 4px; text-align: left; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Student Name</th>
          <th style="width: 18%; padding: 6px 4px; text-align: left; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Student #</th>
          <th style="width: 15%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Course</th>
          <th style="width: 15%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Year Level</th>
          <th style="width: 20%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Attendance Standing</th>
        </tr>
      </thead>
      <tbody>
        ${targetStudents.length === 0 ? `
          <tr><td colspan="5" style="text-align: center; padding: 15px; color: #64748b; font-size: 9px; border: 1px solid #cbd5e1;">No student records found.</td></tr>
        ` : targetStudents.map((s, idx) => {
          const absences = s.total_absences || 0;
          const standingText = absences === 0 ? 'Regular Attendee (0 Absences)' : `Irregular Attendee (${absences} Absence${absences > 1 ? 's' : ''})`;
          return `
          <tr style="background-color: ${idx % 2 === 1 ? '#f8fafc' : '#ffffff'};">
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; font-weight: 700; color: #1e293b; font-size: 8px; word-break: break-word;">${escapeHtml(s.full_name)}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; font-family: monospace;">${escapeHtml(s.student_number)}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; font-weight: 700; color: #002d56; font-size: 8px; text-align: center;">${escapeHtml(s.course_code)}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; text-align: center;">${formatYearLevelDisplay(s.year_level)}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; text-align: center; font-weight: 700; color: ${absences === 0 ? '#065f46' : '#991b1b'}; font-size: 7.5px;">${standingText}</td>
          </tr>
          `;
        }).join('')}
      </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: flex-start;">
      <div style="width: 44%; text-align: center;">
        <div style="border-bottom: 1.5px solid #002d56; height: 26px; margin-bottom: 5px;"></div>
        <p style="font-weight: 700; font-size: 10px; color: #002d56; margin: 0;">${escapeHtml(deanName)}</p>
        <p style="font-size: 8.5px; color: #64748b; margin: 2px 0 0 0;">Dean of Student Affairs / Department Head</p>
      </div>
    </div>

    <div style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 5px; text-align: center; font-size: 7.5px; color: #94a3b8;">
      &copy; ${new Date().getFullYear()} College of Information Technology &bull; Official Computer Generated Institutional Attendance &amp; Standing Record
    </div>
  `;

  offscreenContainer.appendChild(reportWrapper);
  document.body.appendChild(offscreenContainer);

  // Ensure all images in the document are fully loaded before converting
  const reportImgs = Array.from(reportWrapper.querySelectorAll('img'));
  await Promise.all(reportImgs.map(img => {
    if (img.complete) return Promise.resolve();
    return new Promise(resolve => {
      img.onload = resolve;
      img.onerror = resolve;
    });
  }));

  // Check if html2pdf is loaded and download automatically
  if (typeof html2pdf !== 'undefined') {
    const btn = document.querySelector('button[onclick="generatePDFReport()"]');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) btn.innerHTML = 'Downloading PDF...';

    const opt = {
      margin: [10, 8, 10, 8],
      filename: pdfFilename,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        logging: false
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: [] }
    };

    try {
      await html2pdf().set(opt).from(reportWrapper).save();
    } catch (err) {
      console.error('html2pdf direct export failed:', err);
      openPrintWindowFallback(reportWrapper.innerHTML, pdfFilename);
    } finally {
      if (offscreenContainer && offscreenContainer.parentNode) {
        offscreenContainer.parentNode.removeChild(offscreenContainer);
      }
      if (btn) btn.innerHTML = originalText;
    }
  } else {
    openPrintWindowFallback(reportWrapper.innerHTML, pdfFilename);
    if (offscreenContainer && offscreenContainer.parentNode) {
      offscreenContainer.parentNode.removeChild(offscreenContainer);
    }
  }
}

async function downloadStudentDtrPdf(studentId) {
  const s = (cachedStudents || []).find(st => String(st.student_id) === String(studentId));
  if (!s) return alert('Student details not found.');

  const btn = document.getElementById('drawerDownloadDtrBtn');
  const originalText = btn ? btn.innerHTML : '';
  if (btn) btn.innerHTML = 'Downloading DTR PDF...';

  // Retrieve attendance logs for this student
  let studentLogs = (cachedLogs || []).filter(l => String(l.student_id) === String(studentId));
  if (studentLogs.length === 0) {
    try {
      const res = await fetch(API_BASE + 'admin_get_logs.php' + getDeanQueryParam());
      const data = await res.json();
      if (data.status === 'success' && data.data) {
        cachedLogs = data.data;
        studentLogs = cachedLogs.filter(l => String(l.student_id) === String(studentId));
      }
    } catch (_) { }
  }

  // Sort logs by date descending
  studentLogs.sort((a, b) => new Date(b.date) - new Date(a.date));

  const deanName = currentUser ? currentUser.full_name : 'Dean Admin';
  const dateTag = new Date().toISOString().slice(0, 10);
  const cleanName = (s.full_name || 'Student').replace(/[^a-zA-Z0-9]/g, '_');
  const filename = `DTR_${cleanName}_${s.student_number || 'Record'}_${dateTag}.pdf`;
  const logoUrl = getLogoDataUrl();

  const offscreenContainer = document.createElement('div');
  offscreenContainer.style.position = 'fixed';
  offscreenContainer.style.top = '0';
  offscreenContainer.style.left = '0';
  offscreenContainer.style.width = '695px';
  offscreenContainer.style.opacity = '0';
  offscreenContainer.style.pointerEvents = 'none';
  offscreenContainer.style.zIndex = '-9999';
  offscreenContainer.style.overflow = 'visible';

  const dtrWrapper = document.createElement('div');
  dtrWrapper.style.boxSizing = 'border-box';
  dtrWrapper.style.width = '695px';
  dtrWrapper.style.padding = '14px 16px';
  dtrWrapper.style.backgroundColor = '#ffffff';
  dtrWrapper.style.color = '#1e293b';
  dtrWrapper.style.fontFamily = "'Helvetica Neue', Helvetica, Arial, sans-serif";
  dtrWrapper.style.fontSize = '10px';
  dtrWrapper.style.lineHeight = '1.35';

  dtrWrapper.innerHTML = `
    <div style="display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 2px solid #002d56; padding-bottom: 8px; margin-bottom: 10px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <img src="${logoUrl}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: contain; flex-shrink: 0;" alt="CIT Logo" onerror="this.style.display='none';">
        <div>
          <div style="font-size: 14px; font-weight: 800; color: #002d56; text-transform: uppercase; letter-spacing: 0.3px; margin: 0;">College of Information Technology</div>
          <div style="font-size: 9.5px; font-weight: 600; color: #475569; margin-top: 2px;">Office of Student Affairs &amp; Academic Records</div>
          <div style="font-size: 10.5px; font-weight: 700; color: #047857; text-transform: uppercase; margin-top: 2px;">Official Daily Time Record (DTR)</div>
        </div>
      </div>
      <div style="text-align: right; font-size: 9px; color: #64748b; flex-shrink: 0; padding-left: 10px;">
        <strong style="color: #1e293b;">Date Issued:</strong><br>
        <span style="white-space: nowrap;">${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
      </div>
    </div>

    <!-- Student Credentials Box -->
    <div style="display: flex; gap: 8px; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 10px; border-radius: 5px; margin-bottom: 10px;">
      <div style="flex: 1; font-size: 9.5px;">
        <div><strong style="color: #002d56;">Student Name:</strong> ${escapeHtml(s.full_name)}</div>
        <div style="margin-top: 2px;"><strong style="color: #002d56;">Student Number:</strong> ${escapeHtml(s.student_number)}</div>
        <div style="margin-top: 2px;"><strong style="color: #002d56;">Academic Program:</strong> ${escapeHtml(s.course_code)} — ${escapeHtml(s.course_name)}</div>
      </div>
      <div style="flex: 1; font-size: 9.5px;">
        <div><strong style="color: #002d56;">Active Facility:</strong> ${escapeHtml(s.site_name)} (${escapeHtml(s.site_location || '')})</div>
        <div style="margin-top: 2px;"><strong style="color: #002d56;">Attendance Standing:</strong> ${(s.total_absences || 0) === 0 ? '<span style="color:#065f46; font-weight:700;">Regular Attendee (0 Absences)</span>' : `<span style="color:#991b1b; font-weight:700;">Irregular Attendee (${s.total_absences || 0} Absence(s))</span>`}</div>
      </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 8.5px;">
      <thead>
        <tr style="background-color: #002d56; color: #ffffff;">
          <th style="width: 22%; padding: 6px 4px; text-align: left; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Date</th>
          <th style="width: 16%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Morning In</th>
          <th style="width: 16%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Morning Out</th>
          <th style="width: 16%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Afternoon In</th>
          <th style="width: 16%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Afternoon Out</th>
          <th style="width: 14%; padding: 6px 4px; text-align: center; font-weight: 700; font-size: 8px; text-transform: uppercase; border: 1px solid #002d56;">Status</th>
        </tr>
      </thead>
      <tbody>
        ${studentLogs.length === 0 ? `
          <tr><td colspan="6" style="padding: 15px; text-align: center; color: #64748b; font-size: 9px; border: 1px solid #cbd5e1;">No attendance logs recorded yet.</td></tr>
        ` : studentLogs.map((l, idx) => `
          <tr style="background-color: ${idx % 2 === 1 ? '#f8fafc' : '#ffffff'};">
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; font-weight: 700; color: #1e293b; font-size: 8px;">${l.date}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; text-align: center;">${l.time_in_morning || '—'}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; text-align: center;">${l.time_out_morning || '—'}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; text-align: center;">${l.time_in_afternoon || '—'}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; color: #475569; font-size: 8px; text-align: center;">${l.time_out_afternoon || '—'}</td>
            <td style="border: 1px solid #cbd5e1; padding: 5px 4px; text-align: center;">
              <span style="display: inline-block; padding: 2px 4px; border-radius: 3px; font-size: 7.5px; font-weight: 700; background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;">
                ${l.attendance_status || 'Verified'}
              </span>
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>

    <div style="margin-top: 20px; display: flex; justify-content: space-between;">
      <div style="width: 44%; text-align: center;">
        <div style="border-bottom: 1.5px solid #002d56; height: 26px; margin-bottom: 5px;"></div>
        <p style="font-weight: 700; font-size: 10px; color: #002d56; margin: 0;">${escapeHtml(s.full_name)}</p>
        <p style="font-size: 8.5px; color: #64748b; margin: 2px 0 0 0;">Student Signature</p>
      </div>
      <div style="width: 44%; text-align: center;">
        <div style="border-bottom: 1.5px solid #002d56; height: 26px; margin-bottom: 5px;"></div>
        <p style="font-weight: 700; font-size: 10px; color: #002d56; margin: 0;">${escapeHtml(deanName)}</p>
        <p style="font-size: 8.5px; color: #64748b; margin: 2px 0 0 0;">Dean / Academic Records</p>
      </div>
    </div>

    <div style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 5px; text-align: center; font-size: 7.5px; color: #94a3b8;">
      &copy; ${new Date().getFullYear()} College of Information Technology &bull; Official Computer Generated Daily Time Record
    </div>
  `;

  offscreenContainer.appendChild(dtrWrapper);
  document.body.appendChild(offscreenContainer);

  const dtrImgs = Array.from(dtrWrapper.querySelectorAll('img'));
  await Promise.all(dtrImgs.map(img => {
    if (img.complete) return Promise.resolve();
    return new Promise(resolve => {
      img.onload = resolve;
      img.onerror = resolve;
    });
  }));

  try {
    if (typeof html2pdf !== 'undefined') {
      const opt = {
        margin: [10, 8, 10, 8],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          logging: false
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: [] }
      };
      await html2pdf().set(opt).from(dtrWrapper).save();
    } else {
      openPrintWindowFallback(dtrWrapper.innerHTML, filename);
    }
  } catch (err) {
    console.error('Error exporting student DTR PDF:', err);
    openPrintWindowFallback(dtrWrapper.innerHTML, filename);
  } finally {
    if (offscreenContainer && offscreenContainer.parentNode) {
      offscreenContainer.parentNode.removeChild(offscreenContainer);
    }
    if (btn) btn.innerHTML = originalText;
  }
}

function openPrintWindowFallback(innerHtml, title) {
  const printWin = window.open('', '_blank', 'width=900,height=750');
  if (printWin) {
    printWin.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <meta charset="UTF-8">
        <title>${title}</title>
        <style>
          @page { size: A4 portrait; margin: 12mm; }
          body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 10px; }
        </style>
      </head>
      <body>
        ${innerHtml}
        <script>
          window.onload = function() {
            window.print();
          };
        <\/script>
      </body>
      </html>
    `);
    printWin.document.close();
  } else {
    alert('Please allow popups to view and print the document.');
  }
}

function exportCSVReport() {
  const targetStudents = getReportStudents();
  if (targetStudents.length === 0) return alert('No data to export.');
  let csv = 'Student Name,Student Number,Course,Year Level,Attendance Standing\n';
  targetStudents.forEach(s => {
    const absences = s.total_absences || 0;
    const standing = absences === 0 ? 'Regular Attendee (0 Absences)' : `Irregular Attendee (${absences} Absence${absences > 1 ? 's' : ''})`;
    csv += `"${s.full_name}","${s.student_number}","${s.course_code}","${formatYearLevelDisplay(s.year_level)}","${standing}"\n`;
  });

  const blob = new Blob([csv], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `CIT_Attendance_Standing_Report_${selectedReportCourse}_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
}


// Facial Verification Photo Modal Viewer
function openPhotoModal(photos, attendanceId = null) {
  const container = document.getElementById('photoModalContent');
  const confirmContainer = document.getElementById('photoModalConfirmContainer');
  if (!container) return;

  if (attendanceId) {
    markNotificationViewed('log_' + attendanceId);
    updateNotifications();
  }

  currentModalPhotos = Array.isArray(photos) ? photos : [];
  currentModalPhotoIndex = 0;

  const logRecord = attendanceId ? (cachedLogs || []).find(x => x.attendance_id == attendanceId) : null;

  if (currentModalPhotos.length === 0) {
    container.innerHTML = `
      <div style="padding: 2.5rem 1.5rem; text-align: center; color: var(--text-muted); background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--border-light);">
        <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">📷</div>
        <div style="font-weight: 700; color: var(--navy-primary); font-size: 0.95rem; margin-bottom: 0.25rem;">No Photos Recorded</div>
        <p style="font-size: 0.8rem; margin: 0; color: #64748b;">No facial verification photos recorded for this attendance log.</p>
      </div>`;
  } else {
    container.innerHTML = currentModalPhotos.map((p, idx) => {
      const isMorning = (p.shift_type || '').toLowerCase().includes('morning');
      const shiftKey = isMorning ? 'morning' : 'afternoon';
      const shiftName = isMorning ? 'Morning Shift' : 'Afternoon Shift';

      let shiftStatus = 'Pending';
      let badgeClass = 'badge-warning';
      if (logRecord) {
        const s = isMorning ? logRecord.morning_status : logRecord.afternoon_status;
        if (s === 'Confirmed') {
          shiftStatus = 'Confirmed';
          badgeClass = 'badge-success';
        } else if (s === 'Rejected') {
          shiftStatus = 'Rejected';
          badgeClass = 'badge-danger';
        } else {
          shiftStatus = 'Pending';
          badgeClass = 'badge-warning';
        }
      }

      const evalHtml = attendanceId ? `
        <div style="padding: 0.65rem 0.85rem; background: #f8fafc; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 0.45rem;">
            <span style="font-weight: 700; font-size: 0.8rem; color: var(--navy-primary); display: flex; align-items: center; gap: 0.25rem;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
              Evaluate Verification:
            </span>
            <span style="font-weight: 700; font-size: 0.78rem; color: #334155;">${shiftName}</span>
            <span class="badge ${badgeClass}" style="font-size: 0.68rem; padding: 0.15rem 0.45rem;">${shiftStatus}</span>
          </div>
          <div style="display: flex; gap: 0.35rem;">
            <button type="button" class="btn btn-success" style="font-size: 0.75rem; padding: 0.32rem 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;" onclick="reviewAttendanceLog(${attendanceId}, 'Confirmed', '${shiftKey}'); closePhotoModal();">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
              Confirm
            </button>
            <button type="button" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.32rem 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;" onclick="reviewAttendanceLog(${attendanceId}, 'Rejected', '${shiftKey}'); closePhotoModal();">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              Reject
            </button>
          </div>
        </div>
      ` : '';

      return `
        <div style="margin-bottom: 1rem; border: 1px solid var(--border-light); border-radius: var(--radius-sm); overflow: hidden; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
          <div style="padding: 0.55rem 0.85rem; background-color: var(--bg-canvas); font-weight: 700; font-size: 0.8rem; color: var(--navy-primary); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light);">
            <div style="display: flex; align-items: center; gap: 0.45rem;">
              <span class="badge" style="font-size: 0.72rem; font-weight: 700; background: ${isMorning ? '#e0f2fe' : '#fef3c7'}; color: ${isMorning ? '#0369a1' : '#b45309'}; border: 1px solid ${isMorning ? '#bae6fd' : '#fde68a'}; padding: 0.2rem 0.5rem; border-radius: 4px;">Shift: ${escapeHtml(p.shift_type)}</span>
              <span style="color: var(--text-muted); font-size: 0.76rem;">Captured at ${escapeHtml(p.captured_at)}</span>
            </div>
            <button type="button" class="btn-view-full-pill" onclick="openModalPhotoByIndex(${idx})" title="Click to view whole picture">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
              View Whole Picture
            </button>
          </div>
          <div class="photo-preview-wrapper" onclick="openModalPhotoByIndex(${idx})" title="Click to view whole picture (Full Size)">
            <img src="${resolvePhotoUrl(p.full_url, p.image_path)}" class="verification-preview-img" alt="Shift: ${escapeHtml(p.shift_type)}" onerror="handlePhotoError(this, '${escapeHtml(p.full_url && p.full_url !== resolvePhotoUrl(p.full_url, p.image_path) ? p.full_url : '')}');">
            <div class="photo-hover-overlay">
              <span class="photo-hover-badge">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                Click to view whole picture
              </span>
            </div>
          </div>
          ${evalHtml}
        </div>
      `;
    }).join('');
  }

  if (confirmContainer) {
    if (attendanceId) {
      const logRecord = (cachedLogs || []).find(x => x.attendance_id == attendanceId);
      const hasM = logRecord ? ((logRecord.time_in_morning && logRecord.time_in_morning !== '--:--') || (logRecord.time_out_morning && logRecord.time_out_morning !== '--:--')) : true;
      const hasA = logRecord ? ((logRecord.time_in_afternoon && logRecord.time_in_afternoon !== '--:--') || (logRecord.time_out_afternoon && logRecord.time_out_afternoon !== '--:--')) : true;

      let modalActionsHtml = '';
      if (hasM) {
        const mStatus = logRecord ? (logRecord.morning_status || 'Pending') : 'Pending';
        const mBadge = mStatus === 'Confirmed' ? 'badge-success' : (mStatus === 'Rejected' ? 'badge-danger' : 'badge-warning');
        modalActionsHtml += `
          <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.65rem; flex: 1; min-width: 140px; background: #f8fafc;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
              <span style="font-weight: 700; font-size: 0.78rem; color: #1e293b;">Morning Shift</span>
              <span class="badge ${mBadge}" style="font-size: 0.65rem;">${mStatus}</span>
            </div>
            <div style="display: flex; gap: 0.35rem;">
              <button type="button" class="btn btn-success" style="font-size: 0.75rem; padding: 0.28rem 0.65rem; font-weight: 700; flex: 1;" onclick="reviewAttendanceLog(${attendanceId}, 'Confirmed', 'morning'); closePhotoModal();">Confirm</button>
              <button type="button" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.28rem 0.65rem; font-weight: 700; flex: 1;" onclick="reviewAttendanceLog(${attendanceId}, 'Rejected', 'morning'); closePhotoModal();">Reject</button>
            </div>
          </div>
        `;
      }
      if (hasA) {
        const aStatus = logRecord ? (logRecord.afternoon_status || 'Pending') : 'Pending';
        const aBadge = aStatus === 'Confirmed' ? 'badge-success' : (aStatus === 'Rejected' ? 'badge-danger' : 'badge-warning');
        modalActionsHtml += `
          <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.45rem 0.65rem; flex: 1; min-width: 140px; background: #f8fafc;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
              <span style="font-weight: 700; font-size: 0.78rem; color: #1e293b;">Afternoon Shift</span>
              <span class="badge ${aBadge}" style="font-size: 0.65rem;">${aStatus}</span>
            </div>
            <div style="display: flex; gap: 0.35rem;">
              <button type="button" class="btn btn-success" style="font-size: 0.75rem; padding: 0.28rem 0.65rem; font-weight: 700; flex: 1;" onclick="reviewAttendanceLog(${attendanceId}, 'Confirmed', 'afternoon'); closePhotoModal();">Confirm</button>
              <button type="button" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.28rem 0.65rem; font-weight: 700; flex: 1;" onclick="reviewAttendanceLog(${attendanceId}, 'Rejected', 'afternoon'); closePhotoModal();">Reject</button>
            </div>
          </div>
        `;
      }
      // Note: "Entire Day" button has been completely removed. Only Morning and Afternoon shift evaluations are supported.

      confirmContainer.innerHTML = modalActionsHtml ? `
        <div style="display: flex; flex-direction: column; gap: 0.35rem; width: 100%;">
          <div style="font-size: 0.78rem; font-weight: 700; color: var(--navy-primary);">Shift Actions Summary:</div>
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            ${modalActionsHtml}
          </div>
        </div>` : '';
    } else {
      confirmContainer.innerHTML = '';
    }
  }

  document.getElementById('photoModal').classList.add('active');
}

function closePhotoModal() {
  document.getElementById('photoModal').classList.remove('active');
}

function openModalPhotoByIndex(idx) {
  if (!currentModalPhotos || !currentModalPhotos[idx]) return;
  currentModalPhotoIndex = idx;
  const p = currentModalPhotos[idx];
  const shiftLabel = p.shift_type ? `Shift: ${p.shift_type}` : 'Verification Photo';
  const timeLabel = p.captured_at ? `Captured at ${p.captured_at}` : '';
  const photoUrl = resolvePhotoUrl(p.full_url, p.image_path);
  const fallbackUrl = (p.full_url && p.full_url !== photoUrl) ? p.full_url : '';
  openImageLightbox(photoUrl, shiftLabel, timeLabel, currentModalPhotos, idx, fallbackUrl);
}

function openLiveCaptureLightbox(idx) {
  if (!cachedLiveCaptures || !cachedLiveCaptures[idx]) return;
  const c = cachedLiveCaptures[idx];
  const title = `${c.full_name} (${c.student_number}) • Shift: ${c.shift_type}`;
  const subtitle = `Captured at ${c.captured_time} (${c.date}) • Facility: ${c.site_name || 'Assigned Site'}`;
  const photoUrl = resolvePhotoUrl(c.full_url, c.image_path);
  const fallbackUrl = (c.full_url && c.full_url !== photoUrl) ? c.full_url : '';
  openImageLightbox(photoUrl, title, subtitle, null, -1, fallbackUrl);
}

// Lightbox Rotation & Mirror Transformation State
let currentLightboxRotation = 0;
let currentLightboxMirrored = false;

function applyLightboxTransform() {
  const img = document.getElementById('imageLightboxImg');
  const resetBtn = document.getElementById('lightboxResetBtn');
  const mirrorBtn = document.getElementById('lightboxMirrorBtn');
  if (!img) return;

  const deg = ((currentLightboxRotation % 360) + 360) % 360;
  const isMirrored = currentLightboxMirrored;
  const scaleX = isMirrored ? -1 : 1;

  img.style.transform = `rotate(${deg}deg) scaleX(${scaleX})`;
  img.style.transition = 'transform 0.25s cubic-bezier(0.4, 0, 0.2, 1)';

  // If rotated sideways (90 or 270 deg), adjust dimensions so it fits nicely
  const isSideways = (deg === 90 || deg === 270);
  if (isSideways) {
    img.style.maxHeight = '72vw';
    img.style.maxWidth = '68vh';
  } else {
    img.style.maxHeight = '72vh';
    img.style.maxWidth = '100%';
  }

  if (resetBtn) {
    const isTransformed = (deg !== 0 || isMirrored);
    resetBtn.style.display = isTransformed ? 'inline-flex' : 'none';
  }

  if (mirrorBtn) {
    if (isMirrored) {
      mirrorBtn.style.background = 'rgba(245, 158, 11, 0.25)';
      mirrorBtn.style.borderColor = '#f59e0b';
      mirrorBtn.style.color = '#f59e0b';
    } else {
      mirrorBtn.style.background = '';
      mirrorBtn.style.borderColor = '';
      mirrorBtn.style.color = '';
    }
  }
}

function rotateLightbox(deltaDegrees) {
  currentLightboxRotation = (currentLightboxRotation + deltaDegrees) % 360;
  if (currentLightboxRotation < 0) currentLightboxRotation += 360;
  applyLightboxTransform();
}

function toggleLightboxMirror() {
  currentLightboxMirrored = !currentLightboxMirrored;
  applyLightboxTransform();
}

function resetLightboxTransform() {
  currentLightboxRotation = 0;
  currentLightboxMirrored = false;
  applyLightboxTransform();
}

// Lightbox Core Functions
function openImageLightbox(imageUrl, title = 'Facial Verification Photo', subtitle = '', galleryItems = null, activeIndex = -1, fallbackUrl = '') {
  const modal = document.getElementById('imageLightboxModal');
  const img = document.getElementById('imageLightboxImg');
  const titleEl = document.getElementById('imageLightboxTitle');
  const subtitleEl = document.getElementById('imageLightboxSubtitle');
  const externalBtn = document.getElementById('imageLightboxExternalBtn');
  const prevBtn = document.getElementById('lightboxPrevBtn');
  const nextBtn = document.getElementById('lightboxNextBtn');
  const counterEl = document.getElementById('lightboxCounter');

  if (!modal || !img) return;

  currentLightboxGallery = Array.isArray(galleryItems) && galleryItems.length > 1 ? galleryItems : null;
  currentLightboxIndex = activeIndex;

  resetLightboxTransform();

  img.style.objectFit = 'contain';
  img.style.backgroundColor = '#071b30';

  img.onerror = function () {
    if (fallbackUrl && this.src !== fallbackUrl && !this.dataset.triedLightboxAlt) {
      this.dataset.triedLightboxAlt = '1';
      this.src = fallbackUrl;
      return;
    }
    this.onerror = null;
    this.src = DEFAULT_STUDENT_AVATAR_FALLBACK;
  };
  img.dataset.triedLightboxAlt = '';
  img.src = imageUrl || DEFAULT_STUDENT_AVATAR_FALLBACK;

  if (titleEl) titleEl.textContent = title;
  if (subtitleEl) subtitleEl.textContent = subtitle;
  if (externalBtn) externalBtn.href = imageUrl || '#';

  if (currentLightboxGallery && currentLightboxIndex >= 0) {
    if (prevBtn) prevBtn.style.display = 'inline-flex';
    if (nextBtn) nextBtn.style.display = 'inline-flex';
    if (counterEl) {
      counterEl.style.display = 'inline-block';
      counterEl.textContent = `${currentLightboxIndex + 1} / ${currentLightboxGallery.length}`;
    }
  } else {
    if (prevBtn) prevBtn.style.display = 'none';
    if (nextBtn) nextBtn.style.display = 'none';
    if (counterEl) counterEl.style.display = 'none';
  }

  modal.classList.add('active');
}

function navigateLightbox(direction) {
  if (!currentLightboxGallery || currentLightboxGallery.length <= 1) return;
  let newIdx = currentLightboxIndex + direction;
  if (newIdx < 0) newIdx = currentLightboxGallery.length - 1;
  if (newIdx >= currentLightboxGallery.length) newIdx = 0;

  openModalPhotoByIndex(newIdx);
}

function closeImageLightbox() {
  const modal = document.getElementById('imageLightboxModal');
  if (modal) {
    modal.classList.remove('active');
  }
  resetLightboxTransform();
}

function handleLightboxBackdropClick(event) {
  if (event.target.id === 'imageLightboxModal' || event.target.classList.contains('image-lightbox-body')) {
    closeImageLightbox();
  }
}

async function reviewAttendanceLog(attendanceId, action, shift = 'morning') {
  let shiftLabel = 'Morning Shift';
  if (shift === 'afternoon') shiftLabel = 'Afternoon Shift';
  if (shift === 'both') shiftLabel = 'Entire Day';

  const log = (cachedLogs || []).find(x => x.attendance_id == attendanceId);
  const hasMIn = log && log.time_in_morning && log.time_in_morning !== '--:--';
  const hasMOut = log && log.time_out_morning && log.time_out_morning !== '--:--';
  const hasAIn = log && log.time_in_afternoon && log.time_in_afternoon !== '--:--';
  const hasAOut = log && log.time_out_afternoon && log.time_out_afternoon !== '--:--';

  let defaultPrompt = '';
  if (action === 'Rejected') {
    if (shift === 'morning') {
      if (hasMIn && hasMOut) {
        defaultPrompt = 'Morning Shift taken off-site';
      } else if (hasMOut) {
        defaultPrompt = 'Morning Time-Out taken off-site';
      } else if (hasMIn) {
        defaultPrompt = 'Morning Time-In taken off-site';
      } else {
        defaultPrompt = 'Morning Shift taken off-site';
      }
    } else if (shift === 'afternoon') {
      if (hasAIn && hasAOut) {
        defaultPrompt = 'Afternoon Shift taken off-site';
      } else if (hasAOut) {
        defaultPrompt = 'Afternoon Time-Out taken off-site';
      } else if (hasAIn) {
        defaultPrompt = 'Afternoon Time-In taken off-site';
      } else {
        defaultPrompt = 'Afternoon Shift taken off-site';
      }
    } else {
      defaultPrompt = 'Selfie photo invalid / not on-site';
    }
  } else {
    defaultPrompt = (shift === 'morning' ? 'Morning shift verified and confirmed' : (shift === 'afternoon' ? 'Afternoon shift verified and confirmed' : 'Attendance log confirmed by Dean of Student Affairs'));
  }

  const remarks = prompt(`[${shiftLabel}] Enter Dean Administrative Remarks (${action}):\n(Type the exact remark you want to record and show to the student)`, defaultPrompt);
  if (remarks === null) return; // Dean clicked cancel

  try {
    const res = await fetch(API_BASE + 'admin_confirm_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        attendance_id: attendanceId,
        dean_id: currentUser ? currentUser.user_id : 0,
        action: action,
        shift: shift,
        remarks: remarks.trim()
      })
    });
    const data = await res.json();
    if (data.status === 'success') {
      markNotificationViewed('log_' + attendanceId);
      updateNotifications();
      alert(data.message);
      fetchLogs();
      fetchOverview();
      fetchStudents();
    } else {
      alert(data.message || 'Operation failed.');
    }
  } catch (err) {
    console.error('Review attendance log error:', err);
    alert('Server error processing request.');
  }
}

// ==================== TAG GROUP ATTENDANCE MODAL LOGIC ====================
async function openTagGroupModal(attendanceId, preferredShift = null) {
  const log = (cachedLogs || []).find(x => x.attendance_id == attendanceId);
  if (!log) {
    alert('Attendance log record not found.');
    return;
  }

  currentTagAttendanceId = attendanceId;
  tagModalActivePhotos = log.photos || [];

  // 1. Ensure cachedStudents is fully loaded with all enrolled students
  if (!cachedStudents || cachedStudents.length === 0 || !cachedStudents.find(s => Number(s.student_id) === Number(log.student_id))) {
    try {
      const res = await fetch(API_BASE + 'admin_get_students.php?all=1');
      const data = await res.json();
      if (data.status === 'success' && data.data) {
        cachedStudents = data.data;
      }
    } catch (e) {
      console.error('Error fetching students for tag modal:', e);
    }
  }

  const hasMorning = (log.time_in_morning && log.time_in_morning !== '--:--') || (log.time_out_morning && log.time_out_morning !== '--:--');
  const hasAfternoon = (log.time_in_afternoon && log.time_in_afternoon !== '--:--') || (log.time_out_afternoon && log.time_out_afternoon !== '--:--');

  // Determine initial shift
  if (preferredShift && ['morning', 'morning_in', 'morning_out', 'afternoon', 'afternoon_in', 'afternoon_out', 'both'].includes(preferredShift)) {
    currentTagShift = preferredShift;
  } else if (hasMorning && !hasAfternoon) {
    currentTagShift = (log.time_out_morning && log.time_out_morning !== '--:--') ? 'morning_out' : 'morning_in';
  } else if (!hasMorning && hasAfternoon) {
    currentTagShift = (log.time_out_afternoon && log.time_out_afternoon !== '--:--') ? 'afternoon_out' : 'afternoon_in';
  } else if (hasMorning && hasAfternoon) {
    if (log.morning_status === 'Pending' && log.afternoon_status === 'Pending') {
      currentTagShift = 'both';
    } else if (log.morning_status === 'Pending') {
      currentTagShift = (log.time_out_morning && log.time_out_morning !== '--:--') ? 'morning_out' : 'morning_in';
    } else {
      currentTagShift = (log.time_out_afternoon && log.time_out_afternoon !== '--:--') ? 'afternoon_out' : 'afternoon_in';
    }
  } else {
    currentTagShift = 'morning_in';
  }

  // Fallback resolve submitter ID if not in log object
  let submitterId = Number(log.student_id || 0);
  if (!submitterId && log.student_number) {
    const matched = (cachedStudents || []).find(s => s.student_number === log.student_number || s.full_name === log.full_name);
    if (matched) submitterId = Number(matched.student_id);
  }
  if (submitterId > 0) {
    log.student_id = submitterId;
  }

  // Pre-select the submitter
  taggedStudentIds = new Set();
  if (submitterId > 0) {
    taggedStudentIds.add(submitterId);
  }

  // Set Subtitle
  const subEl = document.getElementById('tagModalSubtitle');
  if (subEl) {
    subEl.textContent = `Submitted by ${log.full_name} (${log.course_code || 'Student'}) • Date: ${log.date}`;
  }

  // 2. Build 4 photo slots (Morning In, Morning Out, Afternoon In, Afternoon Out)
  buildTagPhotoSlots(log);

  // 3. Render Shift & Punch Selector Bar
  renderTagShiftSelector(log, hasMorning, hasAfternoon);

  // 4. Update slide photos based on selected punch/shift
  updateTagSlidePhotos();

  // 5. Reset search and filter
  const searchInput = document.getElementById('tagStudentSearchInput');
  if (searchInput) searchInput.value = '';
  const courseFilter = document.getElementById('tagCourseFilterSelect');
  if (courseFilter) courseFilter.value = 'ALL';
  const yearFilter = document.getElementById('tagYearLevelFilterSelect') || document.getElementById('tagSiteFilterSelect');
  if (yearFilter) yearFilter.value = 'ALL';

  // 6. Render Student Roster with student names
  renderTagStudentList();

  // Show Modal
  const modal = document.getElementById('tagGroupModal');
  if (modal) modal.classList.add('active');
}

function buildTagPhotoSlots(log) {
  const photos = log.photos || [];

  const findPhoto = (keywords) => {
    return photos.find(p => {
      const st = (p.shift_type || '').toLowerCase();
      return keywords.some(k => st.includes(k));
    }) || null;
  };

  const mInPhoto = findPhoto(['morning in', 'morning_in']) || (photos[0] && (photos[0].shift_type || '').toLowerCase().includes('morning') ? photos[0] : null);
  const mOutPhoto = findPhoto(['morning out', 'morning_out']) || (photos[1] && (photos[1].shift_type || '').toLowerCase().includes('morning') ? photos[1] : null);
  const aInPhoto = findPhoto(['afternoon in', 'afternoon_in']) || (photos[2] && (photos[2].shift_type || '').toLowerCase().includes('afternoon') ? photos[2] : null);
  const aOutPhoto = findPhoto(['afternoon out', 'afternoon_out']) || (photos[3] && (photos[3].shift_type || '').toLowerCase().includes('afternoon') ? photos[3] : null);

  tagPhotoSlots = [
    {
      index: 0,
      label: 'Morning In',
      shift: 'morning',
      target: 'morning_in',
      time: log.time_in_morning && log.time_in_morning !== '--:--' ? log.time_in_morning : '--:--',
      photo: mInPhoto || photos[0] || null
    },
    {
      index: 1,
      label: 'Morning Out',
      shift: 'morning',
      target: 'morning_out',
      time: log.time_out_morning && log.time_out_morning !== '--:--' ? log.time_out_morning : '--:--',
      photo: mOutPhoto || (photos.length > 1 ? photos[1] : null)
    },
    {
      index: 2,
      label: 'Afternoon In',
      shift: 'afternoon',
      target: 'afternoon_in',
      time: log.time_in_afternoon && log.time_in_afternoon !== '--:--' ? log.time_in_afternoon : '--:--',
      photo: aInPhoto || (photos.length > 2 ? photos[2] : null)
    },
    {
      index: 3,
      label: 'Afternoon Out',
      shift: 'afternoon',
      target: 'afternoon_out',
      time: log.time_out_afternoon && log.time_out_afternoon !== '--:--' ? log.time_out_afternoon : '--:--',
      photo: aOutPhoto || (photos.length > 3 ? photos[3] : null)
    }
  ];
}

function updateTagSlidePhotos() {
  let matched = [];

  switch (currentTagShift) {
    case 'morning_in':
      matched = tagPhotoSlots.filter(s => s.target === 'morning_in');
      break;
    case 'morning_out':
      matched = tagPhotoSlots.filter(s => s.target === 'morning_out');
      break;
    case 'morning':
      matched = tagPhotoSlots.filter(s => s.shift === 'morning' && s.photo);
      if (matched.length === 0) {
        matched = tagPhotoSlots.filter(s => s.shift === 'morning');
      }
      break;
    case 'afternoon_in':
      matched = tagPhotoSlots.filter(s => s.target === 'afternoon_in');
      break;
    case 'afternoon_out':
      matched = tagPhotoSlots.filter(s => s.target === 'afternoon_out');
      break;
    case 'afternoon':
      matched = tagPhotoSlots.filter(s => s.shift === 'afternoon' && s.photo);
      if (matched.length === 0) {
        matched = tagPhotoSlots.filter(s => s.shift === 'afternoon');
      }
      break;
    case 'both':
    default:
      matched = tagPhotoSlots.filter(s => s.photo);
      if (matched.length === 0) {
        matched = tagPhotoSlots;
      }
      break;
  }

  activeSlidePhotos = matched;
  currentSlidePhotoIndex = 0;
  renderCurrentSlidePhoto();
}

function renderCurrentSlidePhoto() {
  const previewImg = document.getElementById('tagModalPreviewImg');
  const slideLabel = document.getElementById('tagPhotoSlideLabel');
  const slideCounter = document.getElementById('tagPhotoSlideCounter');
  const btnPrev = document.getElementById('btnTagPhotoPrev');
  const btnNext = document.getElementById('btnTagPhotoNext');
  const noPhotoPlaceholder = document.getElementById('tagPhotoNoPlaceholder');

  const total = activeSlidePhotos.length;

  if (total === 0) {
    if (previewImg) previewImg.style.display = 'none';
    if (slideLabel) slideLabel.textContent = 'No Punch Photo';
    if (slideCounter) slideCounter.textContent = '0 of 0';
    if (btnPrev) btnPrev.style.display = 'none';
    if (btnNext) btnNext.style.display = 'none';
    if (noPhotoPlaceholder) {
      noPhotoPlaceholder.style.display = 'flex';
      noPhotoPlaceholder.innerHTML = `<div style="text-align:center;"><div style="font-size: 1.6rem; opacity: 0.7; margin-bottom: 4px;">📷</div><div style="font-weight: 600; font-size: 0.82rem;">No photo evidence captured for this selection</div></div>`;
    }
    return;
  }

  if (currentSlidePhotoIndex < 0) currentSlidePhotoIndex = 0;
  if (currentSlidePhotoIndex >= total) currentSlidePhotoIndex = total - 1;

  const current = activeSlidePhotos[currentSlidePhotoIndex];

  // Update floating label (e.g. Morning In — 05:44 AM)
  if (slideLabel) {
    slideLabel.textContent = `${current.label} — ${current.time}`;
  }

  // Update floating counter (e.g. Photo 1 of 2)
  if (slideCounter) {
    slideCounter.textContent = `Photo ${currentSlidePhotoIndex + 1} of ${total}`;
  }

  // Prev / Next controls: Only show when there are 2 or more photos to toggle through!
  const hasMultiple = total > 1;
  if (btnPrev) btnPrev.style.display = hasMultiple ? 'flex' : 'none';
  if (btnNext) btnNext.style.display = hasMultiple ? 'flex' : 'none';

  // Display Image or Fallback
  if (current.photo && (current.photo.full_url || current.photo.image_path)) {
    if (previewImg) {
      const pUrl = resolvePhotoUrl(current.photo.full_url, current.photo.image_path);
      previewImg.src = pUrl;
      previewImg.style.display = 'block';
      previewImg.onerror = function () {
        handlePhotoError(this, current.photo.full_url);
      };
    }
    if (noPhotoPlaceholder) noPhotoPlaceholder.style.display = 'none';
  } else {
    if (previewImg) {
      previewImg.src = '';
      previewImg.style.display = 'none';
    }
    if (noPhotoPlaceholder) {
      noPhotoPlaceholder.style.display = 'flex';
      noPhotoPlaceholder.innerHTML = `<div style="text-align:center;"><div style="font-size: 1.6rem; opacity: 0.7; margin-bottom: 4px;">📷</div><div style="font-weight: 600; font-size: 0.82rem;">No photo evidence uploaded for ${current.label}</div></div>`;
    }
  }
}

function navigateTagPhotoSlide(delta) {
  if (activeSlidePhotos.length <= 1) return;
  currentSlidePhotoIndex += delta;
  if (currentSlidePhotoIndex < 0) currentSlidePhotoIndex = activeSlidePhotos.length - 1;
  if (currentSlidePhotoIndex >= activeSlidePhotos.length) currentSlidePhotoIndex = 0;
  renderCurrentSlidePhoto();
}

function renderTagShiftSelector(log, hasMorning, hasAfternoon) {
  const container = document.getElementById('tagShiftSelectorContainer');
  if (!container) return;

  const mInTime = log.time_in_morning && log.time_in_morning !== '--:--' ? log.time_in_morning : '';
  const mOutTime = log.time_out_morning && log.time_out_morning !== '--:--' ? log.time_out_morning : '';
  const aInTime = log.time_in_afternoon && log.time_in_afternoon !== '--:--' ? log.time_in_afternoon : '';
  const aOutTime = log.time_out_afternoon && log.time_out_afternoon !== '--:--' ? log.time_out_afternoon : '';

  let html = '';

  if (hasMorning) {
    html += `
      <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: 6px; padding: 0.35rem 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
        <div style="font-size: 0.68rem; font-weight: 800; color: var(--navy-primary); text-transform: uppercase; letter-spacing: 0.3px; display: flex; justify-content: space-between;">
          <span>Morning Shift</span>
          <span style="color: #64748b; font-weight: 600;">${mInTime || '--:--'} – ${mOutTime || '--:--'}</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
          ${mInTime ? `
            <label class="tag-shift-pill ${currentTagShift === 'morning_in' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="morning_in" ${currentTagShift === 'morning_in' ? 'checked' : ''} onchange="onTagShiftChange('morning_in')">
              <span>Morning In (${mInTime})</span>
            </label>
          ` : ''}
          ${mOutTime ? `
            <label class="tag-shift-pill ${currentTagShift === 'morning_out' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="morning_out" ${currentTagShift === 'morning_out' ? 'checked' : ''} onchange="onTagShiftChange('morning_out')">
              <span>Morning Out (${mOutTime})</span>
            </label>
          ` : ''}
          ${(mInTime && mOutTime) ? `
            <label class="tag-shift-pill ${currentTagShift === 'morning' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="morning" ${currentTagShift === 'morning' ? 'checked' : ''} onchange="onTagShiftChange('morning')">
              <span>Full Morning Shift</span>
            </label>
          ` : ''}
        </div>
      </div>
    `;
  }

  if (hasAfternoon) {
    html += `
      <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: 6px; padding: 0.35rem 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
        <div style="font-size: 0.68rem; font-weight: 800; color: var(--navy-primary); text-transform: uppercase; letter-spacing: 0.3px; display: flex; justify-content: space-between;">
          <span>Afternoon Shift</span>
          <span style="color: #64748b; font-weight: 600;">${aInTime || '--:--'} – ${aOutTime || '--:--'}</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
          ${aInTime ? `
            <label class="tag-shift-pill ${currentTagShift === 'afternoon_in' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="afternoon_in" ${currentTagShift === 'afternoon_in' ? 'checked' : ''} onchange="onTagShiftChange('afternoon_in')">
              <span>Afternoon In (${aInTime})</span>
            </label>
          ` : ''}
          ${aOutTime ? `
            <label class="tag-shift-pill ${currentTagShift === 'afternoon_out' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="afternoon_out" ${currentTagShift === 'afternoon_out' ? 'checked' : ''} onchange="onTagShiftChange('afternoon_out')">
              <span>Afternoon Out (${aOutTime})</span>
            </label>
          ` : ''}
          ${(aInTime && aOutTime) ? `
            <label class="tag-shift-pill ${currentTagShift === 'afternoon' ? 'active' : ''}">
              <input type="radio" name="tagShiftRadio" value="afternoon" ${currentTagShift === 'afternoon' ? 'checked' : ''} onchange="onTagShiftChange('afternoon')">
              <span>Full Afternoon Shift</span>
            </label>
          ` : ''}
        </div>
      </div>
    `;
  }

  if (hasMorning && hasAfternoon) {
    html += `
      <div style="display: flex; justify-content: flex-end;">
        <label class="tag-shift-pill ${currentTagShift === 'both' ? 'active' : ''}" style="width: 100%; justify-content: center; padding: 0.35rem 0.6rem;">
          <input type="radio" name="tagShiftRadio" value="both" ${currentTagShift === 'both' ? 'checked' : ''} onchange="onTagShiftChange('both')">
          <span>Both Shifts / Full Day</span>
        </label>
      </div>
    `;
  }

  container.innerHTML = html;
  updateTagPillsHighlight();
  updateTagSubmitButtonLabel();
}

function updateTagPillsHighlight() {
  document.querySelectorAll('.tag-shift-pill').forEach(pill => {
    const inp = pill.querySelector('input[name="tagShiftRadio"]');
    if (inp && inp.checked) {
      pill.classList.add('active');
    } else {
      pill.classList.remove('active');
    }
  });
}

function updateTagSubmitButtonLabel() {
  const btn = document.getElementById('btnSubmitTagAttendance');
  if (!btn) return;

  const log = (cachedLogs || []).find(x => x.attendance_id == currentTagAttendanceId);
  const mIn = log?.time_in_morning && log.time_in_morning !== '--:--' ? log.time_in_morning : '';
  const mOut = log?.time_out_morning && log.time_out_morning !== '--:--' ? log.time_out_morning : '';
  const aIn = log?.time_in_afternoon && log.time_in_afternoon !== '--:--' ? log.time_in_afternoon : '';
  const aOut = log?.time_out_afternoon && log.time_out_afternoon !== '--:--' ? log.time_out_afternoon : '';

  let label = 'Confirm & Credit Attendance';
  switch (currentTagShift) {
    case 'morning_in':
      label = `Confirm & Credit Morning In ${mIn ? '(' + mIn + ')' : ''}`;
      break;
    case 'morning_out':
      label = `Confirm & Credit Morning Out ${mOut ? '(' + mOut + ')' : ''}`;
      break;
    case 'morning':
      label = `Confirm & Credit Morning Full Shift`;
      break;
    case 'afternoon_in':
      label = `Confirm & Credit Afternoon In ${aIn ? '(' + aIn + ')' : ''}`;
      break;
    case 'afternoon_out':
      label = `Confirm & Credit Afternoon Out ${aOut ? '(' + aOut + ')' : ''}`;
      break;
    case 'afternoon':
      label = `Confirm & Credit Afternoon Full Shift`;
      break;
    case 'both':
      label = `Confirm & Credit Full Day (Both Shifts)`;
      break;
  }
  btn.textContent = label;
}

function onTagShiftChange(newShift) {
  currentTagShift = newShift;
  updateTagPillsHighlight();
  updateTagSubmitButtonLabel();
  updateTagSlidePhotos();
}

function renderTagStudentList() {
  const tbody = document.getElementById('tagStudentListTbody');
  if (!tbody) return;

  const searchInput = document.getElementById('tagStudentSearchInput');
  const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const courseFilter = document.getElementById('tagCourseFilterSelect');
  const courseQ = courseFilter ? courseFilter.value : 'ALL';
  const yearFilter = document.getElementById('tagYearLevelFilterSelect') || document.getElementById('tagSiteFilterSelect');
  const yearQ = yearFilter ? yearFilter.value : 'ALL';

  const log = (cachedLogs || []).find(x => x.attendance_id == currentTagAttendanceId);
  let submitterId = log ? Number(log.student_id) : 0;
  if (!submitterId && log && log.student_number) {
    const matched = (cachedStudents || []).find(s => s.student_number === log.student_number || s.full_name === log.full_name);
    if (matched) submitterId = Number(matched.student_id);
  }

  // Make sure submitter is auto-checked
  if (submitterId > 0) {
    taggedStudentIds.add(submitterId);
  }

  // Clone students array
  let sortedStudents = [...(cachedStudents || [])];

  // If submitter is not in sortedStudents, add them at the top
  if (log && submitterId > 0 && !sortedStudents.some(s => Number(s.student_id) === submitterId)) {
    sortedStudents.unshift({
      student_id: submitterId,
      full_name: log.full_name,
      student_number: log.student_number,
      course_code: log.course_code,
      year_level: log.year_level || '4',
      site_name: log.site_name
    });
  }

  // Sort students: submitter first, then alphabetically by full_name
  sortedStudents.sort((a, b) => {
    if (Number(a.student_id) === submitterId) return -1;
    if (Number(b.student_id) === submitterId) return 1;
    return (a.full_name || '').localeCompare(b.full_name || '');
  });

  const filtered = sortedStudents.filter(s => {
    if (courseQ !== 'ALL' && s.course_code !== courseQ) return false;
    if (yearQ !== 'ALL') {
      const sLvl = (s.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      const fLvl = yearQ.toString().trim().replace(/[^0-9]/g, '');
      if (sLvl !== fLvl && !(s.year_level || '').toString().toLowerCase().includes(yearQ.toLowerCase())) return false;
    }
    if (q) {
      const match = (s.full_name && s.full_name.toLowerCase().includes(q)) ||
        (s.student_number && s.student_number.toLowerCase().includes(q)) ||
        (s.course_code && s.course_code.toLowerCase().includes(q)) ||
        (s.site_name && s.site_name.toLowerCase().includes(q));
      if (!match) return false;
    }
    return true;
  });

  // Update visible count label
  const visibleBadge = document.getElementById('tagVisibleCountBadge');
  if (visibleBadge) {
    visibleBadge.textContent = `Showing ${filtered.length} of ${sortedStudents.length} student${sortedStudents.length === 1 ? '' : 's'}`;
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); font-size: 0.85rem;">
          ${q ? `No students match "${escapeHtml(q)}"` : 'No students enrolled.'}
        </td>
      </tr>
    `;
    updateTagSelectedCounter();
    return;
  }

  tbody.innerHTML = filtered.map(s => {
    const isSubmitter = Number(s.student_id) === submitterId;
    const isChecked = taggedStudentIds.has(Number(s.student_id));
    const courseBadge = getCourseBadgeClass(s.course_code);
    const rowClass = `tag-student-row ${isSubmitter ? 'is-submitter' : ''} ${isChecked ? 'is-checked' : ''}`;
    const leftBorder = isSubmitter ? 'border-left: 3px solid #16a34a;' : (isChecked ? 'border-left: 3px solid #2563eb;' : 'border-left: 3px solid transparent;');

    return `
      <tr id="tagRow_${s.student_id}" 
          class="${rowClass}" 
          style="${leftBorder} transition: background 0.15s ease;"
          ${isSubmitter ? '' : `onclick="handleTagRowClick(event, ${s.student_id})"`}>
        <td style="text-align: center; vertical-align: middle; padding: 0.45rem 0.4rem;">
          <input type="checkbox" 
                 id="chkStudent_${s.student_id}" 
                 value="${s.student_id}" 
                 ${isChecked ? 'checked' : ''} 
                 ${isSubmitter ? 'disabled title="Submitter is automatically included"' : `onchange="toggleTaggedStudent(${s.student_id}, this.checked)"`}
                 style="cursor: pointer; width: 17px; height: 17px; accent-color: #1e3a8a;">
        </td>
        <td style="vertical-align: middle; padding: 0.45rem 0.65rem;">
          <div style="font-weight: 700; color: var(--navy-primary); font-size: 0.86rem; display: flex; align-items: center; gap: 0.4rem;">
            ${escapeHtml(s.full_name)}
            ${isSubmitter ? '<span class="badge badge-success" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">Submitter</span>' : ''}
          </div>
        </td>
        <td style="vertical-align: middle; padding: 0.45rem 0.65rem; font-size: 0.82rem; color: var(--text-muted); font-family: monospace; font-weight: 600;">
          ${escapeHtml(s.student_number || '--')}
        </td>
        <td style="vertical-align: middle; padding: 0.45rem 0.65rem;">
          <span class="badge ${courseBadge}" style="font-size: 0.72rem;">${s.course_code || 'N/A'}</span>
        </td>
        <td style="vertical-align: middle; padding: 0.45rem 0.65rem; font-size: 0.8rem; color: #64748b;">
          ${escapeHtml(s.site_name || 'Unassigned')}
        </td>
      </tr>
    `;
  }).join('');

  updateTagSelectedCounter();
}

function handleTagRowClick(event, studentId) {
  if (event.target.tagName === 'INPUT') return;
  const log = (cachedLogs || []).find(x => x.attendance_id == currentTagAttendanceId);
  const submitterId = log ? Number(log.student_id) : 0;
  if (Number(studentId) === submitterId) return;

  const chk = document.getElementById(`chkStudent_${studentId}`);
  if (chk && !chk.disabled) {
    chk.checked = !chk.checked;
    toggleTaggedStudent(studentId, chk.checked);
  }
}

function toggleTaggedStudent(studentId, isChecked) {
  const sid = Number(studentId);
  if (isChecked) {
    taggedStudentIds.add(sid);
  } else {
    taggedStudentIds.delete(sid);
  }
  updateTagSelectedCounter();

  // Instant visual feedback on the row
  const row = document.getElementById(`tagRow_${sid}`);
  if (row && !row.classList.contains('is-submitter')) {
    if (isChecked) {
      row.classList.add('is-checked');
      row.style.borderLeft = '3px solid #2563eb';
    } else {
      row.classList.remove('is-checked');
      row.style.borderLeft = '3px solid transparent';
    }
  }
}

function selectAllFilteredStudents(selectAll) {
  const searchInput = document.getElementById('tagStudentSearchInput');
  const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const courseFilter = document.getElementById('tagCourseFilterSelect');
  const courseQ = courseFilter ? courseFilter.value : 'ALL';
  const yearFilter = document.getElementById('tagYearLevelFilterSelect') || document.getElementById('tagSiteFilterSelect');
  const yearQ = yearFilter ? yearFilter.value : 'ALL';

  const log = (cachedLogs || []).find(x => x.attendance_id == currentTagAttendanceId);
  let submitterId = log ? Number(log.student_id) : 0;
  if (!submitterId && log && log.student_number) {
    const matched = (cachedStudents || []).find(s => s.student_number === log.student_number || s.full_name === log.full_name);
    if (matched) submitterId = Number(matched.student_id);
  }

  const filtered = (cachedStudents || []).filter(s => {
    if (courseQ !== 'ALL' && s.course_code !== courseQ) return false;
    if (yearQ !== 'ALL') {
      const sLvl = (s.year_level || '').toString().trim().replace(/[^0-9]/g, '');
      const fLvl = yearQ.toString().trim().replace(/[^0-9]/g, '');
      if (sLvl !== fLvl && !(s.year_level || '').toString().toLowerCase().includes(yearQ.toLowerCase())) return false;
    }
    if (q) {
      const match = (s.full_name && s.full_name.toLowerCase().includes(q)) ||
        (s.student_number && s.student_number.toLowerCase().includes(q)) ||
        (s.course_code && s.course_code.toLowerCase().includes(q)) ||
        (s.site_name && s.site_name.toLowerCase().includes(q));
      if (!match) return false;
    }
    return true;
  });

  filtered.forEach(s => {
    const sid = Number(s.student_id);
    if (selectAll) {
      taggedStudentIds.add(sid);
    } else {
      if (sid !== submitterId) {
        taggedStudentIds.delete(sid);
      }
    }
  });

  renderTagStudentList();
}

function updateTagSelectedCounter() {
  const badge = document.getElementById('tagSelectedCountBadge');
  if (badge) {
    const count = taggedStudentIds.size;
    badge.textContent = `${count} Student${count === 1 ? '' : 's'} Selected`;
  }
}

function filterTagStudentList() {
  renderTagStudentList();
}

function closeTagGroupModal() {
  const modal = document.getElementById('tagGroupModal');
  if (modal) modal.classList.remove('active');
  currentTagAttendanceId = null;
  taggedStudentIds.clear();
  tagPhotoSlots = [];
  activeSlidePhotos = [];
  currentSlidePhotoIndex = 0;
}

function viewTagPhotoFull() {
  if (activeSlidePhotos[currentSlidePhotoIndex] && activeSlidePhotos[currentSlidePhotoIndex].photo) {
    const cur = activeSlidePhotos[currentSlidePhotoIndex];
    const photoUrl = resolvePhotoUrl(cur.photo.full_url, cur.photo.image_path);
    const fallbackUrl = (cur.photo.full_url && cur.photo.full_url !== photoUrl) ? cur.photo.full_url : '';
    openImageLightbox(photoUrl, `Verification Photo (${cur.label})`, `Captured at ${cur.time}`, null, -1, fallbackUrl);
  }
}

async function submitTagGroupAttendance() {
  if (!currentTagAttendanceId) {
    alert('No reference attendance log selected.');
    return;
  }

  if (taggedStudentIds.size === 0) {
    alert('Please select at least one student present in the photo.');
    return;
  }

  const btn = document.getElementById('btnSubmitTagAttendance');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Crediting Attendance...';
  }

  try {
    const res = await fetch(API_BASE + 'admin_tag_group_attendance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        attendance_id: currentTagAttendanceId,
        shift: currentTagShift,
        tagged_student_ids: Array.from(taggedStudentIds),
        dean_id: currentUser ? currentUser.user_id : 0
      })
    });

    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      closeTagGroupModal();
      fetchLogs();
      fetchOverview();
      fetchStudents();
    } else {
      alert(data.message || 'Error crediting attendance.');
    }
  } catch (err) {
    console.error('Error submitting tag group attendance:', err);
    alert('Server communication error. Please try again.');
  } finally {
    if (btn) {
      btn.disabled = false;
      updateTagSubmitButtonLabel();
    }
  }
}

async function openStudentDrawer(studentId) {
  // 1. Ensure student is found regardless of string vs int ID
  let s = (cachedStudents || []).find(st => String(st.student_id) === String(studentId));

  // If not found in memory cache, try fetching student list once
  if (!s) {
    try {
      const res = await fetch(API_BASE + 'admin_get_students.php' + getDeanQueryParam());
      const data = await res.json();
      if (data.status === 'success' && data.data) {
        cachedStudents = data.data;
        s = cachedStudents.find(st => String(st.student_id) === String(studentId));
      }
    } catch (e) {
      console.error('Error fetching student for drawer:', e);
    }
  }

  if (!s) {
    alert('Student record could not be loaded. Please refresh the page.');
    return;
  }

  // 2. Ensure partner sites and courses are loaded for dropdowns
  if (!cachedSites || cachedSites.length === 0) {
    try {
      const resSites = await fetch(API_BASE + 'admin_sites.php');
      const dataSites = await resSites.json();
      if (dataSites.status === 'success' && dataSites.data) {
        cachedSites = dataSites.data;
      }
    } catch (_) { }
  }
  if (!cachedCourses || cachedCourses.length === 0) {
    try {
      await fetchCourses(false);
    } catch (_) { }
  }

  const container = document.getElementById('studentDetailsContent');
  if (!container) return;

  const courseBadge = getCourseBadgeClass(s.course_code);
  const statusBadgeClass = s.status === 'Completed' ? 'badge-success' : 'badge-warning';

  const drawerAbsences = s.total_absences || 0;
  const isRegular = drawerAbsences === 0;

  container.innerHTML = `
    <div style="padding: 1rem; background-color: var(--bg-canvas); border-radius: var(--radius-sm); border: 1px solid var(--border-light); margin-bottom: 1.25rem;">
      <div style="font-size: 1.15rem; font-weight: 800; color: var(--navy-primary);">${escapeHtml(s.full_name)}</div>
      <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.2rem;">
        Student Number: <strong>${escapeHtml(s.student_number)}</strong> &bull; National ID: <strong>${escapeHtml(s.id_no)}</strong>
      </div>
      <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.15rem;">Email Address: <strong>${escapeHtml(s.email)}</strong></div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
      <div style="border: 1px solid var(--border-light); padding: 0.85rem; border-radius: var(--radius-sm); background: #ffffff;">
        <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">Academic Program</div>
        <div><span class="badge ${courseBadge}">${escapeHtml(s.course_code)}</span></div>
        <div style="font-size: 0.8rem; font-weight: 600; color: var(--navy-primary); margin-top: 0.3rem;">${escapeHtml(s.course_name)}</div>
      </div>

      <div style="border: 1px solid var(--border-light); padding: 0.85rem; border-radius: var(--radius-sm); background: #ffffff;">
        <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">Event Attendance Presence</div>
        <div style="font-size: 1.15rem; font-weight: 800; color: #166534;">${s.total_days || 0} Sessions</div>
        <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem;">Recorded Event Presence</div>
      </div>

      <div style="border: 1px solid ${isRegular ? '#a7f3d0' : '#fecaca'}; background: ${isRegular ? '#ecfdf5' : '#fef2f2'}; padding: 0.85rem; border-radius: var(--radius-sm);">
        <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: ${isRegular ? '#065f46' : '#991b1b'}; margin-bottom: 0.3rem;">Attendance Standing</div>
        <div style="font-size: 1.05rem; font-weight: 800; color: ${isRegular ? '#065f46' : '#991b1b'};">${isRegular ? 'Regular Attendee' : 'Irregular Attendee'}</div>
        <div style="font-size: 0.78rem; color: ${isRegular ? '#047857' : '#b91c1c'}; margin-top: 0.15rem;">${isRegular ? '0 Absences (Perfect Attendance)' : `${drawerAbsences} Absence(s) on Record`}</div>
      </div>
    </div>

    <!-- Dean Controls: Reassign Course -->
    <div style="border: 1px solid var(--border-light); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; background: var(--bg-canvas);">
      <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.6rem;">🎓 Reassign Academic Program</div>
      <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <select id="drawerCourseSelect" style="flex: 1; min-width: 180px; padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 0.85rem; background: #fff; color: var(--navy-primary); font-weight: 600;">
          ${(cachedCourses || []).map(c => `<option value="${c.course_id}" ${c.course_id == s.course_id ? 'selected' : ''}>${escapeHtml(c.course_code)} — ${escapeHtml(c.course_name)}</option>`).join('')}
        </select>
        <button class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.82rem; white-space: nowrap;" onclick="saveStudentAssignedCourse(${s.student_id})">Save Program</button>
      </div>
    </div>

    <!-- Dean Controls: Delete Student Account -->
    <div style="border: 1px solid #fee2e2; padding: 1rem; border-radius: var(--radius-sm); background: #fef2f2; margin-top: 1.25rem;">
      <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #b91c1c; margin-bottom: 0.35rem;">⚠️ Danger Zone: Delete Student Account</div>
      <p style="font-size: 0.78rem; color: #7f1d1d; margin: 0 0 0.75rem 0; line-height: 1.4;">
        Permanently delete this student's account and remove all event attendance logs, facial verification photos, and records from the database.
      </p>
      <button type="button" class="btn btn-danger" style="padding: 0.45rem 0.95rem; font-size: 0.82rem; font-weight: 700;" onclick="deleteStudentAccount(${s.student_id}, '${escapeHtml(s.full_name).replace(/'/g, "\\'")}')">
        Delete Student Account
      </button>
    </div>
  `;

  const modal = document.getElementById('studentDetailsModal');
  if (modal) {
    modal.classList.add('active');
  }

  const dtrBtn = document.getElementById('drawerDownloadDtrBtn');
  if (dtrBtn) {
    dtrBtn.onclick = () => downloadStudentDtrPdf(s.student_id);
  }

  const delBtn = document.getElementById('drawerDeleteStudentBtn');
  if (delBtn) {
    delBtn.onclick = () => deleteStudentAccount(s.student_id, s.full_name);
  }
}

function setDrawerPresetHours(hours) {
  const input = document.getElementById('drawerHoursInput');
  if (input) input.value = hours;
  document.querySelectorAll('.btn-preset-chip').forEach(btn => {
    btn.classList.toggle('active', parseInt(btn.textContent, 10) === hours);
  });
}

function closeStudentDetailsModal() {
  const modal = document.getElementById('studentDetailsModal');
  if (modal) {
    modal.classList.remove('active');
  }
}

window.openStudentDrawer = openStudentDrawer;
window.closeStudentDetailsModal = closeStudentDetailsModal;
window.setDrawerPresetHours = setDrawerPresetHours;
window.downloadStudentDtrPdf = downloadStudentDtrPdf;
window.generatePDFReport = generatePDFReport;
window.openJournalModal = openJournalModal;
window.closeJournalModal = closeJournalModal;
window.saveJournalEvaluation = saveJournalEvaluation;
window.deleteJournal = deleteJournal;
window.deleteAllFilteredJournals = deleteAllFilteredJournals;
window.setJournalDateFilter = setJournalDateFilter;
window.handleJournalDateSelectChange = handleJournalDateSelectChange;
window.handleJournalCustomDateChange = handleJournalCustomDateChange;
window.handleJournalStatusFilterChange = handleJournalStatusFilterChange;
window.handleJournalSiteFilterChange = handleJournalSiteFilterChange;
window.handleJournalCourseFilterChange = handleJournalCourseFilterChange;
window.resetJournalFilters = resetJournalFilters;
window.handleModalDeleteJournal = handleModalDeleteJournal;

function filterActiveTable(query) {
  if (!query) {
    if (currentTab === 'students') filterByCourse(currentCourseFilter, false);
    if (currentTab === 'logs') filterLogsByCourse(currentLogsCourseFilter, false);
    if (currentTab === 'absences') renderAbsencesTable(cachedAbsences);
    if (currentTab === 'journals') applyJournalFilters('');
    if (currentTab === 'sites') renderSitesTable(cachedSites);
    return;
  }

  if (currentTab === 'students') {
    let baseStudents = cachedStudents;
    if (currentCourseFilter !== 'ALL') {
      baseStudents = cachedStudents.filter(s => (s.course_code || '').toUpperCase() === currentCourseFilter.toUpperCase());
    }
    const filtered = baseStudents.filter(s => s.full_name.toLowerCase().includes(query) || s.student_number.toLowerCase().includes(query) || s.course_code.toLowerCase().includes(query) || s.site_name.toLowerCase().includes(query));
    renderStudentsTable(filtered);
  } else if (currentTab === 'logs') {
    applyLogsFilters(query);
  } else if (currentTab === 'absences') {
    const filtered = cachedAbsences.filter(a => a.full_name.toLowerCase().includes(query) || a.student_number.toLowerCase().includes(query) || a.reason.toLowerCase().includes(query));
    renderAbsencesTable(filtered);
  } else if (currentTab === 'journals') {
    applyJournalFilters(query);
  } else if (currentTab === 'sites') {
    const filtered = cachedSites.filter(st => st.site_name.toLowerCase().includes(query) || st.site_code.toLowerCase().includes(query) || st.location.toLowerCase().includes(query));
    renderSitesTable(filtered);
  }
}

// ─── Course Management Functions ─────────────────────────────────────────────

async function fetchCourses(render = true) {
  try {
    const res = await fetch(API_BASE + 'admin_courses.php');
    const data = await res.json();
    if (data.status === 'success') {
      cachedCourses = data.data || [];
      if (render) renderCoursesTable(cachedCourses);
      populateDynamicCourseDropdowns();
    }
  } catch (err) {
    console.error('Fetch courses error:', err);
  }
}

function renderCoursesTable(courses) {
  const tbody = document.getElementById('coursesTableBody');
  if (!tbody) return;

  if (courses.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:2rem;">No academic programs registered yet.</td></tr>`;
    return;
  }

  tbody.innerHTML = courses.map(c => {
    const badgeClass = getCourseBadgeClass(c.course_code);
    return `
      <tr>
        <td><span class="badge ${badgeClass}">${escapeHtml(c.course_code)}</span></td>
        <td style="font-weight:600;color:var(--navy-primary);">${escapeHtml(c.course_name)}</td>
        <td><span class="badge badge-success">${c.enrolled_students} Student(s)</span></td>
        <td>
          <button class="btn btn-outline" style="margin-right:0.4rem;" onclick='openEditCourseModal(${JSON.stringify(c).replace(/'/g, "&apos;")})'>Edit</button>
          <button class="btn btn-danger" style="padding:0.4rem 0.75rem;font-size:0.8rem;" onclick="deleteCourse(${c.course_id}, '${escapeHtml(c.course_code)}', ${c.enrolled_students})">Delete</button>
        </td>
      </tr>
    `;
  }).join('');
}

let activeEditingCourse = null;

function openAddCourseModal() {
  activeEditingCourse = null;
  document.getElementById('courseModalTitle').textContent = 'Add Academic Program';
  document.getElementById('courseIdInput').value = '';
  document.getElementById('courseCodeInput').value = '';
  document.getElementById('courseNameInput').value = '';
  const hoursInput = document.getElementById('courseRequiredHoursInput');
  if (hoursInput) hoursInput.value = '480';
  const delBtn = document.getElementById('deleteCourseModalBtn');
  if (delBtn) delBtn.style.display = 'none';
  document.getElementById('courseModal').classList.add('active');
}

function openEditCourseModal(course) {
  activeEditingCourse = course;
  document.getElementById('courseModalTitle').textContent = 'Edit Academic Program';
  document.getElementById('courseIdInput').value = course.course_id;
  document.getElementById('courseCodeInput').value = course.course_code;
  document.getElementById('courseNameInput').value = course.course_name;
  const hoursInput = document.getElementById('courseRequiredHoursInput');
  if (hoursInput) hoursInput.value = course.required_hours || '480';
  const delBtn = document.getElementById('deleteCourseModalBtn');
  if (delBtn) delBtn.style.display = 'inline-block';
  document.getElementById('courseModal').classList.add('active');
}

function closeCourseModal() {
  document.getElementById('courseModal').classList.remove('active');
}

function setPresetHours(hours) {
  const input = document.getElementById('courseRequiredHoursInput');
  if (input) input.value = hours;
}

function setDrawerPresetHours(hours) {
  const input = document.getElementById('drawerHoursInput');
  if (input) input.value = hours;
}

async function handleModalDeleteCourse() {
  if (!activeEditingCourse) return;
  closeCourseModal();
  await deleteCourse(activeEditingCourse.course_id, activeEditingCourse.course_code, activeEditingCourse.enrolled_students);
}

async function deleteCourse(courseId, courseCode, enrolledCount) {
  if (enrolledCount > 0) {
    alert(`Cannot delete '${courseCode}' — it currently has ${enrolledCount} active student(s) enrolled. Please reassign all students first.`);
    return;
  }
  if (!confirm(`Permanently delete the '${courseCode}' academic program?`)) return;

  try {
    const res = await fetch(API_BASE + 'admin_courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', course_id: courseId })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      fetchCourses();
    } else {
      alert(data.message || 'Failed to delete program.');
    }
  } catch (err) {
    console.error('Delete course error:', err);
  }
}

async function saveCourse(e) {
  e.preventDefault();
  const course_id = document.getElementById('courseIdInput').value;
  const course_code = document.getElementById('courseCodeInput').value.trim().toUpperCase();
  const course_name = document.getElementById('courseNameInput').value.trim();
  const hoursInput = document.getElementById('courseRequiredHoursInput');
  const required_hours = hoursInput ? (parseInt(hoursInput.value, 10) || 480) : 480;

  if (!course_code || !course_name) {
    alert('Please enter both the Program Code and Academic Program Name.');
    return;
  }

  try {
    const res = await fetch(API_BASE + 'admin_courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'save', course_id, course_code, course_name, required_hours })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message);
      closeCourseModal();
      fetchCourses();
    } else {
      alert(data.message || 'Failed to save program.');
    }
  } catch (err) {
    console.error('Save course error:', err);
  }
}

function populateDynamicCourseDropdowns() {
  const allCount = cachedStudents.length;
  const allLogs = cachedLogs.length;

  // Build options HTML for student and logs filters
  const studentOptions = `<option value="ALL">All Programs (${allCount})</option>` +
    cachedCourses.map(c => {
      const cnt = cachedStudents.filter(s => (s.course_code || '').toUpperCase() === c.course_code.toUpperCase()).length;
      return `<option value="${c.course_code}">${c.course_code} — ${c.course_name} (${cnt})</option>`;
    }).join('');

  const logsOptions = `<option value="ALL">All Programs</option>` +
    cachedCourses.map(c => {
      return `<option value="${c.course_code}">${c.course_code} — ${c.course_name}</option>`;
    }).join('');

  const reportOptions = `<option value="ALL">All Programs</option>` +
    cachedCourses.map(c => `<option value="${c.course_code}">${c.course_code} — ${c.course_name}</option>`).join('');

  const sf = document.getElementById('courseSelectFilter');
  if (sf) { const cur = sf.value; sf.innerHTML = studentOptions; sf.value = cachedCourses.some(c => c.course_code === cur) || cur === 'ALL' ? cur : 'ALL'; }

  const lf = document.getElementById('logsCourseSelectFilter');
  if (lf) { const cur = lf.value; lf.innerHTML = logsOptions; lf.value = cachedCourses.some(c => c.course_code === cur) || cur === 'ALL' ? cur : 'ALL'; }

  const rf = document.getElementById('reportCourseFilterSelect');
  if (rf) { const cur = rf.value; rf.innerHTML = reportOptions; rf.value = cachedCourses.some(c => c.course_code === cur) || cur === 'ALL' ? cur : 'ALL'; }

  const af = document.getElementById('absenceCourseSelectFilter');
  if (af) { const cur = af.value; af.innerHTML = reportOptions; af.value = cachedCourses.some(c => c.course_code === cur) || cur === 'ALL' ? cur : 'ALL'; }

  const jf = document.getElementById('journalCourseSelectFilter');
  if (jf) { const cur = jf.value; jf.innerHTML = reportOptions; jf.value = cachedCourses.some(c => c.course_code === cur) || cur === 'ALL' ? cur : 'ALL'; }
}

async function saveStudentAssignedCourse(studentId) {
  const sel = document.getElementById('drawerCourseSelect');
  if (!sel) return;
  const course_id = sel.value;
  try {
    const res = await fetch(API_BASE + 'admin_student_manage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_course', student_id: studentId, course_id })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert('Academic program updated successfully.');
      closeStudentDetailsModal();
      fetchStudents();
    } else {
      alert(data.message || 'Failed to update program.');
    }
  } catch (err) {
    console.error('Save student course error:', err);
  }
}

// Hours customization and site transfers are deprecated in CIT Event Attendance System

async function deleteStudentAccount(studentId, studentName) {
  const nameDisplay = studentName ? `"${studentName}"` : 'this student';
  if (!confirm(`Are you sure you want to permanently delete the account of ${nameDisplay}?\n\nThis will permanently remove the student's profile, event attendance logs, facial verification photos, and records from the database.\n\nThis action CANNOT be undone.`)) {
    return;
  }

  try {
    const res = await fetch(API_BASE + 'admin_student_manage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete_student',
        student_id: parseInt(studentId, 10)
      })
    });
    const data = await res.json();
    if (data.status === 'success') {
      alert(data.message || 'Student account deleted successfully.');
      closeStudentDetailsModal();
      await fetchStudents();
      fetchOverview();
      fetchSites();
      fetchCourses();
      fetchLogs();
    } else {
      alert(data.message || 'Failed to delete student account.');
    }
  } catch (err) {
    console.error('Delete student account error:', err);
    alert('Server error executing student deletion.');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Global Search Handler across active tab tables
function handleGlobalSearch(query) {
  const q = (query || '').trim().toLowerCase();
  if (q.length > 0 && currentTab === 'overview') {
    switchTab('students');
  }
  filterActiveTable(q);
}

// Notifications Bell System with Immediate Viewed/Tapped Dismissal
function getViewedNotificationIds() {
  try {
    const raw = localStorage.getItem('viewed_admin_notifications');
    if (raw) {
      const arr = JSON.parse(raw);
      if (Array.isArray(arr)) return new Set(arr);
    }
  } catch (e) {}
  return new Set();
}

function markNotificationViewed(notifKey) {
  if (!notifKey) return;
  try {
    const set = getViewedNotificationIds();
    set.add(String(notifKey));
    const arr = Array.from(set).slice(-500);
    localStorage.setItem('viewed_admin_notifications', JSON.stringify(arr));
  } catch (e) {}
}

function clearAllNotifications(e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const notifItems = document.querySelectorAll('.notif-item');
  const viewed = getViewedNotificationIds();

  notifItems.forEach(item => {
    const key = item.getAttribute('data-notif-key');
    if (key) viewed.add(key);
  });

  try {
    localStorage.setItem('viewed_admin_notifications', JSON.stringify(Array.from(viewed).slice(-500)));
  } catch (err) {}

  const notifList = document.getElementById('notifList');
  const countBadge = document.getElementById('notifCountBadge');
  const badgeDot = document.getElementById('notifBadgeDot');
  const clearBtn = document.getElementById('clearAllNotifsBtn');

  if (notifList) {
    notifList.innerHTML = `<div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.82rem;">🎉 All caught up! No pending verifications or requests.</div>`;
  }
  if (countBadge) {
    countBadge.textContent = '0 Pending';
    countBadge.className = 'badge badge-warning';
  }
  if (badgeDot) {
    badgeDot.style.display = 'none';
  }
  if (clearBtn) {
    clearBtn.style.display = 'none';
  }
}

function removeNotificationElement(el, notifKey) {
  markNotificationViewed(notifKey);

  const notifList = document.getElementById('notifList');
  const countBadge = document.getElementById('notifCountBadge');
  const badgeDot = document.getElementById('notifBadgeDot');
  const clearBtn = document.getElementById('clearAllNotifsBtn');

  const targetEl = el || (notifKey ? document.querySelector(`[data-notif-key="${notifKey}"]`) : null);

  if (targetEl && targetEl.parentNode) {
    targetEl.parentNode.removeChild(targetEl);
  }

  const remaining = notifList ? notifList.querySelectorAll('.notif-item').length : 0;

  if (countBadge) {
    countBadge.textContent = `${remaining} Pending`;
    countBadge.className = remaining > 0 ? 'badge badge-danger' : 'badge badge-warning';
  }
  if (badgeDot) {
    badgeDot.style.display = remaining > 0 ? 'block' : 'none';
  }
  if (clearBtn) {
    clearBtn.style.display = remaining > 0 ? 'inline-block' : 'none';
  }
  if (remaining === 0 && notifList) {
    notifList.innerHTML = `<div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.82rem;">🎉 All caught up! No pending verifications or requests.</div>`;
  }
}

function handleNotificationClick(type, id, notifKey, e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }

  // Immediately mark viewed & remove from notification bar
  markNotificationViewed(notifKey);
  const targetEl = (e && e.currentTarget) ? e.currentTarget : document.querySelector(`[data-notif-key="${notifKey}"]`);
  removeNotificationElement(targetEl, notifKey);

  // Close the dropdown immediately
  const dropdown = document.getElementById('notifDropdown');
  if (dropdown) dropdown.classList.add('hidden');

  // Immediately navigate to the respective record & open verification
  if (type === 'log') {
    setLogsDateFilter('ALL');
    switchTab('logs');
    if (id) {
      setTimeout(() => {
        const log = (cachedLogs || []).find(l => String(l.attendance_id) === String(id));
        if (log && log.photos && log.photos.length > 0) {
          openPhotoModal(log.photos, id);
        }
      }, 150);
    }
  } else if (type === 'absence') {
    switchTab('absences');
  }
}

function dismissSingleNotification(notifKey, e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }
  const el = (e && e.target) ? e.target.closest('.notif-item') : document.querySelector(`[data-notif-key="${notifKey}"]`);
  if (el) {
    el.classList.add('removing');
    setTimeout(() => {
      removeNotificationElement(el, notifKey);
    }, 150);
  } else {
    removeNotificationElement(null, notifKey);
  }
}

function updateNotifications() {
  const viewed = getViewedNotificationIds();

  const pendingLogs = (cachedLogs || []).filter(l => {
    const isPending = (l.status === 'Pending' || l.status === 'Pending Verification' || (l.attendance_status && (l.attendance_status.toLowerCase() === 'pending' || l.attendance_status.toLowerCase() === 'pending verification')));
    const key = 'log_' + (l.attendance_id || (l.student_id + '_' + l.date));
    return isPending && !viewed.has(key);
  });

  const pendingAbsences = (cachedAbsences || []).filter(a => {
    const isPending = (a.status === 'Pending');
    const key = 'absence_' + (a.absence_id || (a.student_id + '_' + a.date_absent));
    return isPending && !viewed.has(key);
  });

  const totalPending = pendingLogs.length + pendingAbsences.length;

  const badgeDot = document.getElementById('notifBadgeDot');
  const countBadge = document.getElementById('notifCountBadge');
  const notifList = document.getElementById('notifList');
  const clearBtn = document.getElementById('clearAllNotifsBtn');

  if (badgeDot) {
    badgeDot.style.display = totalPending > 0 ? 'block' : 'none';
  }

  if (countBadge) {
    countBadge.textContent = `${totalPending} Pending`;
    countBadge.className = totalPending > 0 ? 'badge badge-danger' : 'badge badge-warning';
  }

  if (clearBtn) {
    clearBtn.style.display = totalPending > 0 ? 'inline-block' : 'none';
  }

  if (notifList) {
    if (totalPending === 0) {
      notifList.innerHTML = `<div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.82rem;">🎉 All caught up! No pending verifications or requests.</div>`;
    } else {
      let itemsHtml = '';

      pendingLogs.forEach(l => {
        const key = 'log_' + (l.attendance_id || (l.student_id + '_' + l.date));
        itemsHtml += `
          <div class="notif-item" data-notif-key="${key}" onclick="handleNotificationClick('log', '${l.attendance_id || ''}', '${key}', event);">
            <div class="notif-item-title">
              <span>📸 Attendance Log</span>
              <div style="display: flex; align-items: center; gap: 6px;">
                <span class="badge badge-warning" style="font-size: 0.65rem;">Pending</span>
                <button type="button" class="notif-dismiss-btn" title="Dismiss from notification bar" onclick="dismissSingleNotification('${key}', event);">&times;</button>
              </div>
            </div>
            <div class="notif-item-desc">
              <strong>${escapeHtml(l.full_name || 'Student')}</strong> logged attendance on ${l.date}. Verification photo ready for review.
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px; font-size: 0.72rem; color: #64748b;">
              <span style="color: #0284c7; font-weight: 600;">Tap to view &amp; verify &rarr;</span>
              <span style="color: #94a3b8; font-size: 0.68rem;">Tap or click to dismiss</span>
            </div>
          </div>`;
      });

      pendingAbsences.forEach(a => {
        const key = 'absence_' + (a.absence_id || (a.student_id + '_' + a.date_absent));
        itemsHtml += `
          <div class="notif-item" data-notif-key="${key}" onclick="handleNotificationClick('absence', '${a.absence_id || ''}', '${key}', event);">
            <div class="notif-item-title">
              <span>📄 Absence Request</span>
              <div style="display: flex; align-items: center; gap: 6px;">
                <span class="badge badge-danger" style="font-size: 0.65rem;">Action Required</span>
                <button type="button" class="notif-dismiss-btn" title="Dismiss from notification bar" onclick="dismissSingleNotification('${key}', event);">&times;</button>
              </div>
            </div>
            <div class="notif-item-desc">
              <strong>${escapeHtml(a.full_name || 'Student')}</strong> requested absence for ${a.date_absent}: "${escapeHtml(a.reason || '')}".
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px; font-size: 0.72rem; color: #64748b;">
              <span style="color: #0284c7; font-weight: 600;">Tap to view &amp; evaluate &rarr;</span>
              <span style="color: #94a3b8; font-size: 0.68rem;">Tap or click to dismiss</span>
            </div>
          </div>`;
      });

      notifList.innerHTML = itemsHtml;
    }
  }
}

function toggleNotifDropdown(e) {
  if (e) e.stopPropagation();
  const dropdown = document.getElementById('notifDropdown');
  if (dropdown) {
    const isOpening = dropdown.classList.contains('hidden');
    dropdown.classList.toggle('hidden');
    if (isOpening) {
      // Notification bar has been opened/viewed by the administrator
      const badgeDot = document.getElementById('notifBadgeDot');
      if (badgeDot) {
        badgeDot.style.display = 'none';
      }
    }
  }
}

document.addEventListener('click', (e) => {
  const dropdown = document.getElementById('notifDropdown');
  const notifBtn = document.getElementById('notifBtn');
  if (dropdown && !dropdown.classList.contains('hidden')) {
    if (!dropdown.contains(e.target) && (!notifBtn || !notifBtn.contains(e.target))) {
      dropdown.classList.add('hidden');
    }
  }
});

// Help & Documentation Modal Handlers
function openHelpModal() {
  const modal = document.getElementById('helpModal');
  if (modal) modal.classList.add('active');
}

function closeHelpModal() {
  const modal = document.getElementById('helpModal');
  if (modal) modal.classList.remove('active');
}

// ─── ACADEMIC YEAR-END BATCH ARCHIVE & STORAGE RESET ─────────────────────────

function downloadBatchArchive() {
  // 1. Export CSV
  exportCSVReport();

  // 2. Generate PDF Report after brief delay
  setTimeout(() => {
    generatePDFReport();
  }, 600);

  alert('📥 School-Year Archive is downloading!\n\nPlease save both the CSV Spreadsheet and PDF Report to the SBC Dean\'s Google Drive or local archive folder before executing a batch purge.');
}

function openBatchResetModal() {
  const modal = document.getElementById('batchResetModal');
  const chk = document.getElementById('chkBackupDownloaded');
  const txt = document.getElementById('txtResetConfirmation');
  const btn = document.getElementById('btnExecuteBatchReset');

  if (chk) chk.checked = false;
  if (txt) txt.value = '';
  if (btn) btn.disabled = true;

  if (modal) modal.classList.add('active');
}

function closeBatchResetModal() {
  const modal = document.getElementById('batchResetModal');
  if (modal) modal.classList.remove('active');
}

function validateBatchResetForm() {
  const chk = document.getElementById('chkBackupDownloaded');
  const txt = document.getElementById('txtResetConfirmation');
  const btn = document.getElementById('btnExecuteBatchReset');

  const isChecked = chk ? chk.checked : false;
  const isConfirmed = txt ? (txt.value.trim().toUpperCase() === 'RESET') : false;

  if (btn) {
    btn.disabled = !(isChecked && isConfirmed);
  }
}

async function executeBatchReset() {
  const chk = document.getElementById('chkBackupDownloaded');
  const txt = document.getElementById('txtResetConfirmation');

  if (!chk || !chk.checked || !txt || txt.value.trim().toUpperCase() !== 'RESET') {
    alert('Please confirm that you have saved the archive backup and type RESET.');
    return;
  }

  const btn = document.getElementById('btnExecuteBatchReset');
  const originalText = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span> Purging Cohort & Freeing Storage...';
  }

  try {
    const res = await fetch(API_BASE + 'admin_year_end_reset.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        confirmation_code: 'RESET',
        dean_id: currentUser ? currentUser.user_id : 0,
        scope: 'all_students'
      })
    });

    const data = await res.json();
    if (data.status === 'success') {
      closeBatchResetModal();
      alert(`✓ ${data.message}`);
      fetchAllData();
    } else {
      alert(`⚠️ ${data.message || 'Batch reset failed.'}`);
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }
  } catch (err) {
    console.error('Batch reset error:', err);
    alert('Server error executing annual batch reset.');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }
}

window.downloadBatchArchive = downloadBatchArchive;
window.openBatchResetModal = openBatchResetModal;
window.closeBatchResetModal = closeBatchResetModal;
window.validateBatchResetForm = validateBatchResetForm;
window.executeBatchReset = executeBatchReset;
window.handleStudentFilterChange = handleStudentFilterChange;
window.applyStudentFilters = applyStudentFilters;
window.applyAbsenceFilters = applyAbsenceFilters;
window.filterReportByCourse = filterReportByCourse;
window.filterReportByStatus = filterReportByStatus;
window.filterReportBySite = filterReportBySite;
window.deleteStudentAccount = deleteStudentAccount;

// ==========================================
// RFID / SCHOOL ID CARD MAPPING MODAL
// ==========================================
let activeMappingStudentId = null;

function openRfidMappingModal(studentId, fullName, studentNumber, currentRfid, courseCode) {
  activeMappingStudentId = studentId;
  const modal = document.getElementById('rfidMappingModal');
  const nameEl = document.getElementById('rfidModalStudentName');
  const detailsEl = document.getElementById('rfidModalStudentDetails');
  const inputEl = document.getElementById('rfidModalInput');
  const msgEl = document.getElementById('rfidModalStatusMsg');

  if (nameEl) nameEl.textContent = fullName;
  if (detailsEl) detailsEl.textContent = `${studentNumber} • ${courseCode || 'Student'}${currentRfid ? ` (Current RFID: ${currentRfid})` : ' (No RFID Mapped)'}`;
  if (inputEl) {
    inputEl.value = currentRfid || '';
    setTimeout(() => {
      inputEl.focus();
      inputEl.select();
    }, 150);
  }
  if (msgEl) {
    msgEl.className = 'badge hidden';
    msgEl.textContent = '';
  }

  if (modal) {
    modal.classList.add('active');
    modal.style.display = 'flex';
  }
}

function closeRfidMappingModal() {
  const modal = document.getElementById('rfidMappingModal');
  if (modal) {
    modal.classList.remove('active');
    modal.style.display = 'none';
  }
  activeMappingStudentId = null;
}

async function saveStudentRfid() {
  if (!activeMappingStudentId) return;
  const inputEl = document.getElementById('rfidModalInput');
  const msgEl = document.getElementById('rfidModalStatusMsg');
  const rfidVal = (inputEl ? inputEl.value : '').trim();

  if (!rfidVal) {
    if (msgEl) {
      msgEl.className = 'badge badge-danger';
      msgEl.textContent = 'Please tap or enter an RFID card UID.';
      msgEl.classList.remove('hidden');
    }
    return;
  }

  try {
    const res = await fetch(API_BASE + 'tap_attendance_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'map_rfid',
        student_id: activeMappingStudentId,
        rfid_uid: rfidVal
      })
    });
    const data = await res.json();
    if (data.status === 'success') {
      if (msgEl) {
        msgEl.className = 'badge badge-success';
        msgEl.textContent = data.message || 'RFID card successfully mapped!';
        msgEl.classList.remove('hidden');
      }
      await fetchStudents();
      setTimeout(closeRfidMappingModal, 1100);
    } else {
      if (msgEl) {
        msgEl.className = 'badge badge-danger';
        msgEl.textContent = data.message || 'Failed to map RFID card.';
        msgEl.classList.remove('hidden');
      }
    }
  } catch (err) {
    if (msgEl) {
      msgEl.className = 'badge badge-danger';
      msgEl.textContent = 'Network error saving RFID card.';
      msgEl.classList.remove('hidden');
    }
  }
}

async function clearStudentRfid() {
  if (!activeMappingStudentId) return;
  if (!confirm('Are you sure you want to remove the mapped RFID card from this student?')) return;

  const msgEl = document.getElementById('rfidModalStatusMsg');
  try {
    const res = await fetch(API_BASE + 'admin_student_manage.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'update_rfid',
        student_id: activeMappingStudentId,
        rfid_uid: ''
      })
    });
    const data = await res.json();
    if (data.status === 'success') {
      await fetchStudents();
      closeRfidMappingModal();
    } else {
      alert(data.message || 'Error clearing RFID card.');
    }
  } catch (e) {
    alert('Network error while clearing RFID card.');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const rfidInput = document.getElementById('rfidModalInput');
  if (rfidInput) {
    rfidInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        saveStudentRfid();
      }
    });
  }
});

window.openRfidMappingModal = openRfidMappingModal;
window.closeRfidMappingModal = closeRfidMappingModal;
window.saveStudentRfid = saveStudentRfid;
window.clearStudentRfid = clearStudentRfid;



