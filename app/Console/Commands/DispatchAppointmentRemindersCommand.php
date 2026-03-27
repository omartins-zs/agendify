<?php

namespace App\Console\Commands;

use App\Services\ReminderDispatcherService;
use Illuminate\Console\Command;

class DispatchAppointmentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:dispatch-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha lembretes 24h e 2h antes para agendamentos ativos';

    /**
     * Execute the console command.
     */
    public function handle(ReminderDispatcherService $dispatcher): int
    {
        $result = $dispatcher->dispatch();

        $this->info('Lembretes 24h enfileirados: '.$result['24h']);
        $this->info('Lembretes 2h enfileirados: '.$result['2h']);

        return self::SUCCESS;
    }
}
