<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_back_to_back_appointments_do_not_block_the_next_slot(): void
    {
        $user = User::factory()->create();

        Appointment::create([
            'user_id' => $user->id,
            'title' => 'Existing appointment',
            'start_datetime' => '2026-07-20 09:30:00',
            'end_datetime' => '2026-07-20 10:00:00',
            'timezone' => 'UTC',
            'status' => 'confirmed',
            'booked_via' => 'public_form',
        ]);

        $service = app(AvailabilityService::class);

        $this->assertFalse($service->isSlotBooked(
            $user->id,
            Carbon::parse('2026-07-20 10:00:00'),
            Carbon::parse('2026-07-20 10:30:00'),
        ));
    }

    public function test_overlapping_appointments_still_block_the_slot(): void
    {
        $user = User::factory()->create();

        Appointment::create([
            'user_id' => $user->id,
            'title' => 'Existing appointment',
            'start_datetime' => '2026-07-20 09:45:00',
            'end_datetime' => '2026-07-20 10:15:00',
            'timezone' => 'UTC',
            'status' => 'confirmed',
            'booked_via' => 'public_form',
        ]);

        $service = app(AvailabilityService::class);

        $this->assertTrue($service->isSlotBooked(
            $user->id,
            Carbon::parse('2026-07-20 10:00:00'),
            Carbon::parse('2026-07-20 10:30:00'),
        ));
    }
}
