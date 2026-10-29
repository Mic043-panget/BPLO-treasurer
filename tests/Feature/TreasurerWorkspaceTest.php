<?php

namespace Tests\Feature;

use App\Support\TreasurerDemoData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TreasurerWorkspaceTest extends TestCase
{
    public function test_treasurer_screens_render_as_read_only_previews(): void
    {
        foreach (['/treasurer/dashboard', '/treasurer/payments', '/treasurer/history'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Read-only preview.')
                ->assertSee('payment decisions are disabled')
                ->assertSee('Integration pending');
        }
    }

    public function test_preview_does_not_render_payment_decision_controls(): void
    {
        $this->get('/treasurer/payments')
            ->assertOk()
            ->assertDontSee('Verify payment')
            ->assertDontSee('Reject payment')
            ->assertDontSee('Confirm demo action')
            ->assertDontSee('clear-demo-actions');
    }

    public function test_integration_findings_page_is_available(): void
    {
        $this->get('/treasurer/integration')
            ->assertOk()
            ->assertSee('No Treasurer API')
            ->assertSee('Schema mismatch')
            ->assertSee('Current propagation: none')
            ->assertSee('defaults to SQLite')
            ->assertSee('fixture methods are not an allowed-method list');
    }

    public function test_permit_and_qr_information_is_unavailable_without_a_trusted_source(): void
    {
        $this->get('/treasurer/integration')
            ->assertOk()
            ->assertSee('PermitService::release')
            ->assertSee('PAYMENT_VERIFIED')
            ->assertSee('NOT_FOUND')
            ->assertSee('permit status as unavailable')
            ->assertSee('cannot write either record');

        foreach (TreasurerDemoData::payments() as $payment) {
            $this->assertNull($payment['permitNumber']);
            $this->assertArrayNotHasKey('verificationToken', $payment);
            $this->assertArrayNotHasKey('qrCode', $payment);
            $this->assertArrayNotHasKey('qrResult', $payment);
        }

        $this->get('/treasurer/payments')
            ->assertOk()
            ->assertDontSee('BP-2026-00087')
            ->assertSee('Application / permit status');
    }

    public function test_non_paid_demo_payments_are_never_marked_verified(): void
    {
        foreach (TreasurerDemoData::payments() as $payment) {
            if ($payment['paymentStatus'] !== 'PAID') {
                $this->assertNotSame('VERIFIED', $payment['verificationStatus']);
            }
        }
    }

    public function test_treasurer_preview_does_not_expose_admin_settings_or_mutation_routes(): void
    {
        $this->get('/treasurer/payments')
            ->assertOk()
            ->assertSee('Sample method')
            ->assertSee('Sample amount')
            ->assertDontSee('Webhook Secret');

        foreach (['treasurer.settings', 'treasurer.fees', 'treasurer.roles'] as $name) {
            $this->assertFalse(Route::has($name));
        }
    }

    public function test_demo_data_covers_payment_and_verification_outcomes(): void
    {
        $statuses = array_column(TreasurerDemoData::payments(), 'paymentStatus');

        $this->assertContains('PROCESSING', $statuses);
        $this->assertContains('PENDING', $statuses);
        $this->assertContains('PAID', $statuses);
        $this->assertContains('FAILED', $statuses);
        $this->assertContains('CANCELLED', $statuses);
    }

    public function test_no_payment_decision_routes_are_exposed(): void
    {
        $this->assertFalse(Route::has('treasurer.verify'));
        $this->assertFalse(Route::has('treasurer.reject'));

        foreach (['treasurer.dashboard', 'treasurer.payments', 'treasurer.history'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route);
            $this->assertEqualsCanonicalizing(['GET', 'HEAD'], $route->methods());
        }
    }
}
