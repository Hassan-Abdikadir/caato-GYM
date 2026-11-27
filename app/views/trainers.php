<?php
$trActs = '<a href="' . url('classes') . '" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2)"><i class="bi bi-calendar2-week text-info"></i> Workout Classes</a>';
if (!Auth::isSuper()) {
  $trActs .= '<span class="gate-visit-pill active"><i class="bi bi-geo-alt-fill text-danger"></i> ' . e(short_branch(Auth::user()['branch_name'] ?? '')) . ' Coaching Team</span>';
}
if ($canManage) {
  $trActs .= '<button class="btn btn-brand btn-sm px-3 py-2" onclick="tForm()"><i class="bi bi-person-plus-fill"></i> Add Coach / Trainer</button>';
}
$activeTr = count(array_filter($rows, fn($x) => $x['status'] === 'active'));
$totalTrainees = (int) DB::val('SELECT COUNT(*) FROM customers WHERE assigned_trainer_id IS NOT NULL');
page_banner(
  'ATHLETIC COACHING STAFF · PERSONAL TRAINING',
  'Coaches &amp;',
  'Fitness Trainers',
  'Manage certified personal trainers, athletic specializations, trainee assignments & branch placements.',
  $trActs,
  [
    [count($rows), 'Total Coaches', 'info'],
    [$activeTr, 'Active Duty', 'success'],
  ]
);
?>

<!-- 3D Holographic Coaching KPI Strip -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat stat-pro amber">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-trophy-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-trophy-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Roster</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= count($rows) ?> Coaches</div>
        <div class="stat-title">REGISTERED FITNESS TRAINERS</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-stars"></i> Strength · HIIT · Boxing · Nutrition</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:90%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro emerald">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-patch-check-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-patch-check-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> On Floor</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $activeTr ?> Active</div>
        <div class="stat-title">COACHES ON ACTIVE DUTY</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-building-check"></i> Deployed across 6 CAATO branches</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:<?= count($rows) ? round(($activeTr / count($rows)) * 100) : 100 ?>%"></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat stat-pro brand">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-people-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-people-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> Mentorship</span>
        </div>
      </div>
      <div class="stat-body">
        <div class="num"><?= $totalTrainees ?> Athletes</div>
        <div class="stat-title">1-ON-1 ASSIGNED TRAINEES</div>
      </div>
      <div class="stat-foot">
        <span class="stat-sub"><i class="bi bi-heart-pulse-fill"></i> Tracked with Body &amp; BMI metrics</span>
        <div class="stat-spark"><div class="stat-spark-fill" style="width:82%"></div></div>
      </div>
    </div>
  </div>
</div>

<!-- 3D Holographic Coach Cards -->
<div class="row g-3">
<?php
$coachThemes = ['amber', 'brand', 'emerald', 'cyan', 'violet'];
foreach ($rows as $idx => $r):
  $assignedCount = (int) DB::val('SELECT COUNT(*) FROM customers WHERE assigned_trainer_id=?', [$r['id']]);
  $th = $r['status'] === 'active' ? $coachThemes[$idx % count($coachThemes)] : 'red';
  $loadPct = min(100, max(18, $assignedCount * 10));
?>
  <div class="col-md-6 col-xl-4">
    <div class="card stat stat-pro <?= $th ?> h-100">
      <div class="stat-glow-orb"></div>
      <i class="bi bi-trophy-fill stat-watermark"></i>
      <div class="stat-top">
        <div class="ico"><i class="bi bi-trophy-fill"></i></div>
        <div class="stat-meta-right">
          <span class="stat-pill"><span class="stat-dot"></span> <?= e(short_branch($r['branch_name'])) ?></span>
          <?= badge($r['status']) ?>
        </div>
      </div>

      <div class="stat-body my-1">
        <h5 class="fw-bold mb-1"><?= e($r['full_name']) ?></h5>
        <div class="mb-2">
          <span class="badge text-bg-light border"><i class="bi bi-stars text-warning me-1"></i><?= e($r['specialization'] ?: 'General Fitness & Strength') ?></span>
        </div>

        <div class="p-2 px-3 rounded-3 bg-body-tertiary border small d-flex justify-content-between align-items-center">
          <span class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($r['phone']) ?></span>
          <strong class="text-brand"><i class="bi bi-people-fill me-1"></i><?= $assignedCount ?> Trainees</strong>
        </div>
      </div>

      <div class="stat-foot mt-auto">
        <div class="stat-spark mb-2"><div class="stat-spark-fill" style="width:<?= $loadPct ?>%"></div></div>
        <div class="d-flex gap-2">
          <a href="<?= e(whatsapp_url($r['phone'], "Asc Coach {$r['full_name']}!")) ?>" target="_blank" class="btn btn-sm btn-whatsapp" title="WhatsApp Coach">
            <i class="bi bi-whatsapp"></i>
          </a>
          <?php if ($canManage): ?>
            <button class="btn btn-sm btn-outline-brand flex-grow-1" onclick="tForm(<?= jsattr($r) ?>)">
              <i class="bi bi-pencil-square"></i> Edit Profile
            </button>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-warning" data-confirm="Change this trainer's status?">
                <?= $r['status'] === 'active' ? '<i class="bi bi-slash-circle"></i>' : '<i class="bi bi-check-circle"></i>' ?>
              </button>
            </form>
            <form method="post" class="d-inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" data-confirm="Permanently DELETE trainer <?= e($r['full_name']) ?>?">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; if (!$rows): ?>
  <div class="col-12">
    <div class="card card-bento-panel shadow-sm p-5 text-center text-muted">
      <i class="bi bi-trophy panel-watermark"></i>
      <i class="bi bi-trophy fs-1 d-block mb-2 text-warning"></i>
      <h5>No Trainers Registered Yet</h5>
      <p class="small mb-0">Add your personal coaches and floor trainers above.</p>
    </div>
  </div>
<?php endif; ?>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="tModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content shadow-lg" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" id="tid">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-trophy-fill text-warning me-2 fs-5"></i> Trainer Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-12">
          <label class="form-label small fw-semibold">Trainer Full Name *</label>
          <input name="full_name" id="tn" class="form-control" required placeholder="Full name">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Phone Number *</label>
          <input name="phone" id="tp" class="form-control" required placeholder="Phone">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Assigned Branch Facility *</label>
          <select name="branch_id" id="tb" class="form-select" required>
            <?= opts($branches, '', 'id', 'name') ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label small fw-semibold">Specialization &amp; Expertise</label>
          <input name="specialization" id="ts" class="form-control" placeholder="e.g. Bodybuilding, HIIT, Cardio, Nutrition, Boxing…">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-brand"><i class="bi bi-check-circle"></i> Save Trainer Details</button>
      </div>
    </form>
  </div>
</div>

<script>
function tForm(t){
  t = t || {};
  tid.value = t.id || '';
  tn.value = t.full_name || '';
  tp.value = t.phone || '';
  ts.value = t.specialization || '';
  if (t.branch_id) tb.value = t.branch_id;
  new bootstrap.Modal(tModal).show();
}
</script>
<?php endif; ?>
