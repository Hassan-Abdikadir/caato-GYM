<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">Group Classes &amp; Workout Timetable</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> STUDIO &amp; FLOOR SCHEDULE</span>
    </div>
    <small class="text-muted"><i class="bi bi-calendar2-week-fill text-brand me-1"></i> HIIT, CrossFit, Boxing, Strength &amp; Aerobics sessions scheduled across all 6 branches</small>
  </div>
  <div class="d-flex gap-2">
    <?php if (Auth::isSuper()): ?>
    <form method="get">
      <input type="hidden" name="page" value="classes">
      <select name="branch" class="form-select" onchange="this.form.submit()">
        <?= opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches') ?>
      </select>
    </form>
    <?php endif; ?>
    <?php if ($canManage): ?>
      <button class="btn btn-brand" onclick="cForm()">
        <i class="bi bi-plus-circle-fill"></i> Schedule New Class
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
<?php foreach ($rows as $r): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 shadow-sm" style="border-left:4px solid <?= $r['status'] === 'active' ? '#ff5722' : '#64748b' ?>">
      <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="badge text-bg-info"><i class="bi bi-lightning-fill"></i> <?= e($r['category']) ?></span>
          <?= badge($r['status']) ?>
        </div>
        <h4 class="fw-bold mb-1"><?= e($r['title']) ?></h4>
        <div class="small text-muted mb-3">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $r['branch_name'])) ?> · <strong><?= e($r['room']) ?></strong>
        </div>

        <div class="p-3 rounded-3 bg-body-tertiary border small mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-calendar3 me-1"></i>Days</span>
            <strong class="text-brand"><?= e($r['day_of_week']) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-clock me-1"></i>Time</span>
            <strong><?= date('h:i A', strtotime($r['start_time'])) ?> – <?= date('h:i A', strtotime($r['end_time'])) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-trophy-fill text-warning me-1"></i>Lead Coach</span>
            <strong><?= e($r['trainer_name'] ?: 'Floor Coach') ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted"><i class="bi bi-people me-1"></i>Max Capacity</span>
            <strong><?= (int) $r['capacity'] ?> Athletes</strong>
          </div>
        </div>

        <?php if ($canManage): ?>
        <div class="mt-auto d-flex gap-2">
          <button class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="cForm(<?= jsattr($r) ?>)">
            <i class="bi bi-pencil-square"></i> Edit Schedule
          </button>
          <form method="post" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" data-confirm="Remove this workout class from schedule?">
              <i class="bi bi-trash3-fill"></i>
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; if (!$rows): ?>
  <div class="col-12">
    <div class="card shadow-sm p-5 text-center text-muted">
      <i class="bi bi-calendar2-week fs-1 d-block mb-2 text-brand"></i>
      <h5>No Group Classes Scheduled Yet</h5>
      <p class="small mb-3">Create weekly HIIT, Boxing, CrossFit, Aerobics or Spin sessions for your members.</p>
      <?php if ($canManage): ?>
        <div><button class="btn btn-brand btn-sm" onclick="cForm()"><i class="bi bi-plus-lg"></i> Schedule First Class</button></div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="cModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="cid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-calendar2-week-fill text-brand me-2 fs-5"></i> Schedule Group Workout Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-8">
          <label class="form-label small fw-semibold">Class Title *</label>
          <input name="title" id="ctitle" class="form-control" required placeholder="e.g. Evening Fat-Burn HIIT">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Discipline</label>
          <select name="category" id="ccat" class="form-select">
            <option>HIIT &amp; Cardio</option>
            <option>Boxing &amp; Combat</option>
            <option>Strength &amp; CrossFit</option>
            <option>Aerobics &amp; Spin</option>
            <option>Mobility &amp; Core</option>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Facility Branch *</label>
          <select name="branch_id" id="cbr" class="form-select" required>
            <?= opts($branches, '', 'id', 'name') ?>
          </select>
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Lead Coach / Trainer</label>
          <select name="trainer_id" id="ctr" class="form-select">
            <?= opts($trainers, '', 'id', 'full_name', '— Any Floor Coach —') ?>
          </select>
        </div>
        <div class="col-8">
          <label class="form-label small fw-semibold">Days of Week *</label>
          <input name="day_of_week" id="cdays" class="form-control" required placeholder="e.g. Mon, Wed, Fri or Daily">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Studio / Zone</label>
          <input name="room" id="croom" class="form-control" placeholder="Main Studio">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Start Time *</label>
          <input type="time" name="start_time" id="cstart" class="form-control" required value="17:00">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">End Time *</label>
          <input type="time" name="end_time" id="cend" class="form-control" required value="18:00">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold">Capacity</label>
          <input type="number" name="capacity" id="ccap" min="1" value="25" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Class Schedule</button>
      </div>
    </form>
  </div>
</div>

<script>
function cForm(c) {
  c = c || {};
  document.getElementById('cid').value = c.id || '';
  document.getElementById('ctitle').value = c.title || '';
  document.getElementById('ccat').value = c.category || 'HIIT & Cardio';
  if (c.branch_id) document.getElementById('cbr').value = c.branch_id;
  document.getElementById('ctr').value = c.trainer_id || '';
  document.getElementById('cdays').value = c.day_of_week || 'Sat, Mon, Wed';
  document.getElementById('croom').value = c.room || 'Main Studio';
  document.getElementById('cstart').value = (c.start_time || '17:00').substring(0, 5);
  document.getElementById('cend').value = (c.end_time || '18:00').substring(0, 5);
  document.getElementById('ccap').value = c.capacity || 25;
  new bootstrap.Modal(document.getElementById('cModal')).show();
}
</script>
<?php endif; ?>
