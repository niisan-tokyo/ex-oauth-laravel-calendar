<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Niisan\Laravel\GoogleCalendar\OauthCalendarService;
use App\Models\GoogleUser;
use App\Models\Todo;

class SampleCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample:calendar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private OauthCalendarService $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OauthCalendarService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $todo = Todo::first();
        $user = GoogleUser::first();
        $this->service->updateEvent($user, $todo->event_id, [
            'summary' => 'アップデートのテスト',
            'description' => '詳細アップデート',
            'end' => '2021-03-04 12:30:00'
        ]);
    }
}
