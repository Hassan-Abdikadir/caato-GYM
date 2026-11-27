<?php
final class Attendance
{
    /** Cross-branch check-in. The visiting branch is recorded; one check-in per customer per day. */
    public static function checkIn(int $customerId, int $branchId, string $method = 'fingerprint_door'): array
    {
        $c = Customer::find($customerId);
        if (!$c) {
            $msg = 'Customer profile not found.';
            return [false, $msg, 'danger', 'ok' => false, 'message' => $msg, 'level' => 'danger', 'door_status' => 'locked'];
        }

        [$ok, $code, $msg, $lvl] = Membership::access($c);
        $timeStr = date('h:i:s A, d M Y');
        $timeShort = date('h:i:s A');
        $now = date('Y-m-d H:i:s');

        if (!$ok) {
            return [
                false, $msg, $lvl,
                'ok' => false,
                'code' => $code,
                'message' => $msg,
                'level' => $lvl,
                'door_status' => 'locked',
                'customer' => [
                    'id' => (int) $c['id'],
                    'name' => $c['full_name'],
                    'code' => $c['customer_code'],
                    'photo' => photo_url($c['profile_picture']),
                    'status' => $c['eff_status'],
                    'plan' => $c['plan_name'] ?: 'None',
                    'end_date' => fmt_date($c['end_date']),
                ],
                'can_renew' => in_array($c['eff_status'], ['expired', 'none', 'unpaid'], true),
                'renew_url' => url('customer_view', ['id' => $c['id']]) . '#renew'
            ];
        }

        if (!DB::val("SELECT id FROM branches WHERE id=? AND status='active'", [$branchId])) {
            $msg = 'This facility branch is not currently active.';
            return [false, $msg, 'danger', 'ok' => false, 'message' => $msg, 'level' => 'danger', 'door_status' => 'locked'];
        }

        try {
            $data = [
                'customer_id' => $customerId,
                'membership_id' => $c['ms_id'],
                'branch_id' => $branchId,
                'attendance_date' => date('Y-m-d'),
                'check_in_at' => $now,
                'recorded_by' => Auth::id(),
            ];
            try { $data['checkin_method'] = $method; } catch (Throwable $e) {}
            $id = DB::insert('attendance', $data);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $prev = DB::one('SELECT a.check_in_at, b.name FROM attendance a JOIN branches b ON b.id=a.branch_id WHERE a.customer_id=? AND a.attendance_date=CURDATE()', [$customerId]);
                $prevTime = date('h:i A', strtotime($prev['check_in_at'] ?? $now));
                $prevBranch = $prev['name'] ?? 'This branch';
                $dupMsg = "Already checked in today at {$prevTime} ({$prevBranch}). Duplicate door entry blocked.";
                return [
                    false, $dupMsg, 'warning',
                    'ok' => false,
                    'code' => 'already_checked_in',
                    'message' => $dupMsg,
                    'level' => 'warning',
                    'door_status' => 'locked',
                    'check_in_time' => $prevTime,
                    'customer' => [
                        'id' => (int) $c['id'],
                        'name' => $c['full_name'],
                        'code' => $c['customer_code'],
                        'photo' => photo_url($c['profile_picture']),
                        'status' => $c['eff_status'],
                        'plan' => $c['plan_name'] ?: 'None',
                        'last_attendance' => $prev['check_in_at'] ?? null,
                    ]
                ];
            }
            throw $e;
        }

        audit('checkin', 'attendance', $id, "Customer {$c['customer_code']} via $method at branch #$branchId");
        $visit = $branchId != $c['registered_branch_id'] ? " (Visiting from {$c['reg_branch']})" : '';
        $successMsg = "DOOR UNLOCKED / ALBAABKA WAA LA FURAY: Soo dhowow {$c['full_name']}! Xiliga soo galitaanka: {$timeStr}{$visit}.";

        return [
            true, $successMsg, 'success',
            'ok' => true,
            'code' => 'ok',
            'door_status' => 'unlocked',
            'check_in_at' => $now,
            'check_in_time' => $timeShort,
            'check_in_timestamp' => $timeStr,
            'method' => $method,
            'message' => $successMsg,
            'level' => 'success',
            'customer' => [
                'id' => (int) $c['id'],
                'name' => $c['full_name'],
                'code' => $c['customer_code'],
                'photo' => photo_url($c['profile_picture']),
                'plan' => $c['plan_name'],
                'end_date' => fmt_date($c['end_date']),
                'status' => $c['eff_status'],
                'home_branch' => $c['reg_branch'],
            ]
        ];
    }
}
