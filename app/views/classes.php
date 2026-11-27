<?php
$clsActs = '';
if (Auth::isSuper()) {
  $clsActs .= '<form method="get" class="d-inline-block"><input type="hidden" name="page" value="classes"><select name="branch" class="form-select form-select-sm bg-dark text-white border-secondary fw-semibold" onchange="this.form.submit()">' . opts(branch_list(false), $br, 'id', 'name', 'All 6 Branches') . '</select></form>';
} else {
  $clsActs .= '<span class="gate-visit-pill active"><i class="bi bi-geo-alt-fill text-danger"></i> ' . e(short_branch(Auth::user()['branch_name'] ?? '')) . ' Studio Timetable</span>';
}
if ($canManage) {
  $clsActs .= '<button class="btn btn-brand btn-sm px-3 py-2" onclick="cForm()"><i class="bi bi-plus-circle-fill"></i> Schedule New Class</button>';
}
$activeCls = count(array_filter($rows, fn($x) => $x['status'] === 'active'));
$totalCap  = array_sum(array_map(fn($x) => (int) ($x['capacity'] ?? 0), $rows));
page_banner(
  'STUDIO & FLOOR SCHEDULE · GROUP ATHLETIC TRAINING',
  'Classes &amp;',
  'Workout Timetable',
  'Manage HIIT, CrossFit, Boxing, Strength & Aerobics sessions scheduled across all six CAATO facilities.',
  $clsActs,
  [
    [$activeCls, 'Active Classes', 'success'],
    [count($trainers), 'Coaches Ready', 'warning'],
  ]
);
?>

<!-- 3D Holographic Studio KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-calendar2-week-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-calendar2-week-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Timetable</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $activeCls ?> Sessions</div>
        <div class="stat-title">ACTIVE GROUP CLASSES</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-lightning-charge-fill"></i> HIIT · Boxing · CrossFit · Spin</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:88%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro amber">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-trophy-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-trophy-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Coaching Staff</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= count($trainers) ?> Coaches</div>
        <div class="stat-title">CERTIFIED LEAD INSTRUCTORS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-award-fill"></i> Assigned across 6 facility studios</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:92%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro cyan">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-people-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-people-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Studio Capacity</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= (int) $totalCap ?> Spots</div>
        <div class="stat-title">TOTAL ATHLETE CAPACITY</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-building-check"></i> Included with active membership pass</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:85%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- 3D Holographic Studio Class Cards -->
<div class="row g-3">
<?php
$clsMap = [
  'HIIT & Cardio'       => ['ico' => 'bi-fire',                  'theme' => 'brand'],
  'Boxing & Combat'     => ['ico' => 'bi-lightning-charge-fill', 'theme' => 'red'],
  'Strength & CrossFit' => ['ico' => 'bi-trophy-fill',           'theme' => 'amber'],
  'Aerobics & Spin'     => ['ico' => 'bi-bicycle',               'theme' => 'cyan'],
  'Mobility & Core'     => ['ico' => 'bi-heart-pulse-fill',      'theme' => 'emerald'],
];
foreach ($rows as $idx => $r):
  $cm = $clsMap[$r['category']] ?? ['ico' => 'bi-lightning-fill', 'theme' => 'violet'];
  $capPct = min(100, max(20, (int) round(((int) $r['capacity'] / 40) * 100)));
?>
  <div class="col-md-6 col-xl-4">
    <div class="card stat stat-pro <?= $cm['theme'] ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi <?= $cm['ico'] ?> stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi <?= $cm['ico'] ?>"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= e($r['category']) ?></span>
          <?= badge($r['status']) ?>
        </div>
      </div>

      <div class="stat-body my-1">
        <h5 class="fw-bold mb-1"><?= e($r['title']) ?></h5>
        <div class="small text-muted mb-2">
          <span class="gate-visit-pill"><i class="bi bi-geo-alt-fill text-danger"></i> <?= e(short_branch($r['branch_name'])) ?> · <?= e($r['room']) ?></span>
        </div>

        <div class="p-2 px-3 rounded-3 bg-body-tertiary border small">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-calendar3 me-1"></i>Schedule</span>
            <strong class="text-brand"><?= e($r['day_of_week']) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-clock me-1"></i>Session Time</span>
            <strong><?= date('h:i A', strtotime($r['start_time'])) ?> – <?= date('h:i A', strtotime($r['end_time'])) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted"><i class="bi bi-trophy-fill text-warning me-1"></i>Lead Coach</span>
            <strong><?= e($r['trainer_name'] ?: 'Floor Coach') ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted"><i class="bi bi-people-fill me-1"></i>Max Capacity</span>
            <strong><?= (int) $r['capacity'] ?> Athletes</strong>
          </div>
        </div>
      </div>

      <div class="stat-foot mt-auto">
        <div class="stat-spark mb-2"><div class="stat-spark-fill" style="width:<?= $capPct ?>%"></div></div>
        <?php if ($canManage): ?>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-brand flex-grow-1" onclick="cForm(<?= jsattr($r) ?>)">
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
    <div class="card card-bento-panel shadow-sm p-5 text-center text-muted">
      <i class="bi bi-calendar2-week panel-watermark"></i>
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
