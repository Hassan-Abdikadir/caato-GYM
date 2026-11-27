<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
  <div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <h1 class="page-title">Fitness Coaches &amp; Trainers</h1>
      <span class="badge badge-futuristic"><span class="pulse-dot"></span> ATHLETIC COACHING STAFF</span>
    </div>
    <small class="text-muted"><i class="bi bi-trophy-fill text-warning me-1"></i> Manage personal trainers, specializations &amp; assigned facility branches</small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('classes') ?>" class="btn btn-outline-secondary">
      <i class="bi bi-calendar2-week"></i> Workout Classes
    </a>
    <?php if ($canManage): ?>
      <button class="btn btn-brand" onclick="tForm()">
        <i class="bi bi-person-plus-fill"></i> Add Coach / Trainer
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
<?php foreach ($rows as $r):
  $assignedCount = (int) DB::val('SELECT COUNT(*) FROM customers WHERE assigned_trainer_id=?', [$r['id']]);
?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 shadow-sm">
      <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-3 bg-warning bg-opacity-10 text-warning"><i class="bi bi-trophy-fill fs-4"></i></div>
            <div>
              <h5 class="fw-bold mb-0"><?= e($r['full_name']) ?></h5>
              <small class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e(str_replace('CAATO FITNESS GYM – ', '', $r['branch_name'])) ?></small>
            </div>
          </div>
          <?= badge($r['status']) ?>
        </div>

        <div class="my-2">
          <span class="badge text-bg-info"><i class="bi bi-stars me-1"></i><?= e($r['specialization'] ?: 'General Fitness & Strength') ?></span>
        </div>

        <div class="p-2 px-3 rounded-3 bg-body-tertiary border small d-flex justify-content-between align-items-center my-2">
          <span class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($r['phone']) ?></span>
          <strong class="text-brand"><i class="bi bi-people-fill me-1"></i><?= $assignedCount ?> Trainees</strong>
        </div>

        <div class="mt-auto pt-2 d-flex gap-2">
          <a href="<?= e(whatsapp_url($r['phone'], "Asc Coach {$r['full_name']}!")) ?>" target="_blank" class="btn btn-sm btn-whatsapp">
            <i class="bi bi-whatsapp"></i>
          </a>
          <?php if ($canManage): ?>
            <button class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="tForm(<?= jsattr($r) ?>)">
              <i class="bi bi-pencil-square"></i> Edit
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
    <div class="card shadow-sm p-5 text-center text-muted">
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
