<?php

namespace Amicus\FilamentEmployeeManagement\Commands;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveAbsenceInfoNotification;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Console\Command;

class TestAbsenceNotificationCommand extends Command
{
    protected $signature = 'employee:test-absence-notification
        {--type=bolovanje : Tip odsustva (bolovanje, godisnji, placeni_slobodan_dan, porodiljni)}
        {--dry-run : Samo prikaži što bi se poslalo, bez slanja}';

    protected $description = 'Testira slanje obavijesti o odsustvu zaposlenika voditelju za radne sate i direktoru';

    public function handle(): int
    {
        $settings = app(HumanResourcesSettings::class);

        $this->info('Provjera HR postavki...');
        $this->newLine();

        // Provjera voditelja za radne sate
        $approverId = $settings->employee_work_hours_approver_id;
        $approver = $approverId ? Employee::find($approverId) : null;

        if ($approver) {
            $this->line("   Voditelj za radne sate: {$approver->full_name} ({$approver->email})");

            if (! $approver->user) {
                $this->warn('   ⚠ Voditelj za radne sate nema povezan korisnički račun - neće primiti obavijest!');
            }
        } else {
            $this->warn('   ⚠ Voditelj za radne sate nije konfiguriran u HR postavkama.');
        }

        // Provjera direktora
        $directorId = $settings->employee_director_id;
        $director = $directorId ? Employee::find($directorId) : null;

        if ($director) {
            $this->line("   Direktor: {$director->full_name} ({$director->email})");

            if (! $director->user) {
                $this->warn('   ⚠ Direktor nema povezan korisnički račun - neće primiti obavijest!');
            }
        } else {
            $this->warn('   ⚠ Direktor nije konfiguriran u HR postavkama.');
        }

        $this->newLine();

        if (! $approver?->user && ! $director?->user) {
            $this->error('Nema primatelja za obavijesti. Konfigurirajte HR postavke.');

            return Command::FAILURE;
        }

        // Pronađi leave request za testiranje
        $type = $this->option('type');
        $leaveRequest = LeaveRequest::query()
            ->where('status', LeaveRequestStatus::APPROVED)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->with(['employee'])
            ->latest()
            ->first();

        if (! $leaveRequest) {
            $this->error("Nema odobrenih zahtjeva za odsustvo (tip: {$type}). Kreirajte zahtjev prije testiranja.");

            return Command::FAILURE;
        }

        $this->info('Zahtjev za testiranje:');
        $this->line("   Zaposlenik: {$leaveRequest->employee->full_name}");
        $this->line("   Tip: {$leaveRequest->type->getLabel()}");
        $this->line("   Period: {$leaveRequest->start_date->format('d.m.Y')} - {$leaveRequest->end_date->format('d.m.Y')}");
        $this->line("   Broj dana: {$leaveRequest->days_count}");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - obavijesti NISU poslane.');
            $this->newLine();
            $this->printRecipients($approver, $director, $leaveRequest);

            return Command::SUCCESS;
        }

        if (! $this->confirm('Poslati test obavijesti?')) {
            $this->info('Odustali ste.');

            return Command::SUCCESS;
        }

        $sent = 0;

        try {
            if ($approver?->user && $approver->id !== $leaveRequest->employee_id) {
                $approver->user->notify(new LeaveAbsenceInfoNotification($leaveRequest));
                $this->line("   ✅ Poslano voditelju za radne sate: {$approver->full_name} ({$approver->email})");
                $sent++;
            }

            if ($director?->user && $director->id !== $leaveRequest->employee_id) {
                $director->user->notify(new LeaveAbsenceInfoNotification($leaveRequest));
                $this->line("   ✅ Poslano direktoru: {$director->full_name} ({$director->email})");
                $sent++;
            }
        } catch (\Exception $e) {
            $this->error("Greška pri slanju: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info("Ukupno poslano: {$sent} obavijesti. Provjerite queue worker za isporuku.");

        return Command::SUCCESS;
    }

    private function printRecipients(?Employee $approver, ?Employee $director, LeaveRequest $leaveRequest): void
    {
        $this->info('Primatelji bi bili:');

        if ($approver?->user && $approver->id !== $leaveRequest->employee_id) {
            $this->line("   → Voditelj za radne sate: {$approver->full_name} ({$approver->email})");
        } elseif ($approver && $approver->id === $leaveRequest->employee_id) {
            $this->line('   → Voditelj za radne sate: PRESKOČEN (isti zaposlenik)');
        }

        if ($director?->user && $director->id !== $leaveRequest->employee_id) {
            $this->line("   → Direktor: {$director->full_name} ({$director->email})");
        } elseif ($director && $director->id === $leaveRequest->employee_id) {
            $this->line('   → Direktor: PRESKOČEN (isti zaposlenik)');
        }
    }
}
