<?php
$v = $v ?? [];
$g = fn($k) => e($v[$k] ?? '');
$trainersList = [];
try {
    $trainersList = DB::all("SELECT t.id, CONCAT(t.full_name, ' (', b.code, ')') AS label FROM trainers t JOIN branches b ON b.id=t.branch_id WHERE t.status='active' ORDER BY t.full_name");
} catch (Throwable $e) {}
$goals = ['General Fitness', 'Weight Loss & Fat Burn', 'Muscle Hypertrophy & Strength', 'Athletic Conditioning & HIIT', 'Rehabilitation & Mobility', 'Boxing & Combat Fitness'];
?>
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label small fw-semibold">Full Legal Name *</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
      <input name="full_name" class="form-control" required placeholder="Enter member's full name" value="<?= $g('full_name') ?>">
    </div>
  </div>
  <div class="col-md-6">
    <label class="form-label small fw-semibold">Mobile Phone Number * <small class="text-muted">(Unique across all 6 branches)</small></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
      <input name="phone" class="form-control" required placeholder="e.g. 061 / 063 4xx xxxx" value="<?= $g('phone') ?>">
    </div>
  </div>
  <div class="col-md-4">
    <label class="form-label small fw-semibold">Email Address <small class="text-muted">(Welcome Mail)</small></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
      <input type="email" name="email" class="form-control" placeholder="member@email.com" value="<?= $g('email') ?>">
    </div>
  </div>
  <div class="col-md-4">
    <label class="form-label small fw-semibold">Gender *</label>
    <select name="gender" class="form-select" required>
      <option value="">Select gender…</option>
      <?php foreach (['male', 'female'] as $x): ?>
        <option value="<?= $x ?>" <?= ($v['gender'] ?? '') === $x ? 'selected' : '' ?>><?= ucfirst($x) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label small fw-semibold">Date of Birth</label>
    <input type="date" name="dob" max="<?= date('Y-m-d') ?>" class="form-control" value="<?= e($v['dob'] ?? $v['date_of_birth'] ?? '') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label small fw-semibold"><i class="bi bi-bullseye text-brand me-1"></i> Primary Fitness Goal</label>
    <select name="fitness_goal" class="form-select">
      <?php foreach ($goals as $gl): ?>
        <option value="<?= e($gl) ?>" <?= ($v['fitness_goal'] ?? 'General Fitness') === $gl ? 'selected' : '' ?>><?= e($gl) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label small fw-semibold"><i class="bi bi-trophy-fill text-warning me-1"></i> Assigned Personal Coach <small class="text-muted">(Optional)</small></label>
    <select name="assigned_trainer_id" class="form-select">
      <?= opts($trainersList, $v['assigned_trainer_id'] ?? '', 'id', 'label', '— Self-Guided / Any Floor Trainer —') ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label small fw-semibold">Residential Address / District</label>
    <input name="address" class="form-control" placeholder="Neighborhood or district" value="<?= $g('address') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label small fw-semibold"><i class="bi bi-heart-pulse text-danger me-1"></i> Medical / Health Notes <small class="text-muted">(Injuries, Asthma, etc.)</small></label>
    <input name="medical_notes" class="form-control" placeholder="Optional health notes for coaches" value="<?= $g('medical_notes') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label small fw-semibold">Emergency Contact Name</label>
    <input name="emergency_name" class="form-control" placeholder="Next of kin name" value="<?= $g('emergency_name') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label small fw-semibold">Emergency Contact Phone</label>
    <input name="emergency_phone" class="form-control" placeholder="Emergency phone number" value="<?= $g('emergency_phone') ?>">
  </div>

  <div class="col-12">
    <label class="form-label small fw-semibold"><i class="bi bi-fingerprint text-brand me-1"></i> Biometric Fingerprint Door Token <small class="text-muted">(Optional door access ID)</small></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-fingerprint text-brand"></i></span>
      <input name="fingerprint_id" id="fpFieldInput" class="form-control font-monospace" placeholder="Scan finger on USB enrollment reader or click Generate Token…" value="<?= $g('fingerprint_id') ?>">
      <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('fpFieldInput').value='FP-'+Math.floor(100000+Math.random()*900000)">
        <i class="bi bi-magic"></i> Auto-Token
      </button>
    </div>
  </div>

  <div class="col-12">
    <label class="form-label small fw-semibold">Member Profile Photo</label>
    <div class="d-flex gap-2 mb-2">
      <button type="button" id="tabFile" class="btn btn-sm btn-brand" onclick="switchTab('file')">
        <i class="bi bi-upload"></i> Upload Image File
      </button>
      <button type="button" id="tabCam" class="btn btn-sm btn-outline-secondary" onclick="switchTab('cam')">
        <i class="bi bi-camera-video"></i> Capture Live Webcam
      </button>
    </div>

    <div id="panelFile">
      <input type="file" name="photo" id="photoFile" accept="image/jpeg,image/png,image/webp" class="form-control">
    </div>

    <div id="panelCam" style="display:none">
      <div class="d-flex gap-3 flex-wrap align-items-start">
        <div style="position:relative">
          <video id="camVideo" width="260" height="195" autoplay playsinline muted
                 style="border-radius:12px;border:2px solid var(--line-strong);background:#111;display:block"></video>
          <div id="camOverlay" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
               background:rgba(0,0,0,.65);border-radius:12px;color:#fff;font-size:13px;text-align:center;padding:12px">
            <span><i class="bi bi-camera-video" style="font-size:26px;display:block;margin-bottom:6px"></i>
            Click "Open Camera"</span>
          </div>
        </div>
        <div>
          <canvas id="camCanvas" width="260" height="195"
                  style="border-radius:12px;border:2px dashed var(--line-strong);background:rgba(148,163,184,0.08);display:block"></canvas>
          <p id="snapLabel" class="text-muted small mt-1 mb-0" style="text-align:center">Snapshot preview</p>
        </div>
      </div>
      <div class="d-flex gap-2 mt-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-outline-brand" id="btnOpenCam" onclick="openCamera()">
          <i class="bi bi-camera-video-fill"></i> Open Camera
        </button>
        <button type="button" class="btn btn-sm btn-brand" id="btnSnap" onclick="takeSnapshot()" disabled>
          <i class="bi bi-camera"></i> Take Photo
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnRetake" onclick="retake()" style="display:none">
          <i class="bi bi-arrow-counterclockwise"></i> Retake
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnStopCam" onclick="stopCamera()" style="display:none">
          Stop Camera
        </button>
      </div>
      <input type="hidden" name="photo_base64" id="photoBase64">
      <p id="snapStatus" class="text-success small mt-1 mb-0" style="display:none">
        <i class="bi bi-check-circle-fill"></i> Photo captured — ready to submit.
      </p>
    </div>
  </div>
  <script>
  (function(){
    let stream = null;
    window.switchTab = function(tab){
      const isFile = tab === 'file';
      document.getElementById('panelFile').style.display = isFile ? '' : 'none';
      document.getElementById('panelCam').style.display  = isFile ? 'none' : '';
      document.getElementById('tabFile').className = 'btn btn-sm ' + (isFile ? 'btn-brand' : 'btn-outline-secondary');
      document.getElementById('tabCam').className  = 'btn btn-sm ' + (!isFile ? 'btn-brand' : 'btn-outline-secondary');
      if (isFile) {
        stopCamera();
        document.getElementById('photoBase64').value = '';
        document.getElementById('snapStatus').style.display = 'none';
      } else {
        document.getElementById('photoFile').value = '';
      }
    };
    window.openCamera = async function(){
      try {
        stream = await navigator.mediaDevices.getUserMedia({video:{width:640,height:480,facingMode:'user'},audio:false});
        const v = document.getElementById('camVideo');
        v.srcObject = stream;
        document.getElementById('camOverlay').style.display = 'none';
        document.getElementById('btnSnap').disabled = false;
        document.getElementById('btnStopCam').style.display = '';
        document.getElementById('btnOpenCam').style.display = 'none';
      } catch(e) {
        alert('Camera error: ' + e.message + '\n\nMake sure you are accessing the page via http://localhost and have granted camera permission.');
      }
    };
    window.takeSnapshot = function(){
      const v = document.getElementById('camVideo');
      const c = document.getElementById('camCanvas');
      const ctx = c.getContext('2d');
      ctx.drawImage(v, 0, 0, c.width, c.height);
      const data = c.toDataURL('image/jpeg', 0.88);
      document.getElementById('photoBase64').value = data;
      document.getElementById('snapLabel').textContent = 'Photo captured ✓';
      document.getElementById('snapLabel').className = 'text-success small mt-1 mb-0';
      document.getElementById('snapStatus').style.display = '';
      document.getElementById('btnRetake').style.display = '';
      document.getElementById('btnSnap').disabled = true;
      stopCamera();
    };
    window.retake = function(){
      document.getElementById('photoBase64').value = '';
      document.getElementById('snapStatus').style.display = 'none';
      document.getElementById('btnRetake').style.display = 'none';
      document.getElementById('snapLabel').textContent = 'Snapshot preview';
      document.getElementById('snapLabel').className = 'text-muted small mt-1 mb-0';
      const ctx = document.getElementById('camCanvas').getContext('2d');
      ctx.clearRect(0, 0, 260, 195);
      openCamera();
    };
    window.stopCamera = function(){
      if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
      document.getElementById('btnSnap').disabled = true;
      document.getElementById('btnStopCam').style.display = 'none';
      document.getElementById('btnOpenCam').style.display = '';
      document.getElementById('camOverlay').style.display = 'flex';
      const v = document.getElementById('camVideo');
      if (v) v.srcObject = null;
    };
    document.addEventListener('DOMContentLoaded', function(){
      const form = document.querySelector('form[enctype]');
      if (form) form.addEventListener('submit', stopCamera);
    });
  })();
  </script>
</div>
