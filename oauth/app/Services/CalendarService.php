<?php
namespace App\Services;

use App\Models\GoogleUser;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Log;
use Niisan\Laravel\GoogleCalendar\OauthCalendarService;

class CalendarService
{
    private OauthCalendarService $oauthCalendarService;

    public function __construct(OauthCalendarService $oauthCalendarService)
    {
        $this->oauthCalendarService = $oauthCalendarService;
    }

    /**
     * イベントのリストの取得
     * 
     * @param GoogleUser $user
     * 
     * @return array
     */
    public function getEventList(GoogleUser $user)
    {
        $events = $this->oauthCalendarService->getEventList($user, [
            'timeMin' => Carbon::now()->subDays(7)->format('Y-m-d H:i:s'),
            'timeMax' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
        ]);
        $ret = [];
        foreach ($events as $event) {
            if ($event->start and $event->end) {
                $ret[] = [
                    'id' => $event->id,
                    'summary' => $event->getSummary(),
                    'start' => $event->start->dateTime,
                    'end' => $event->end->dateTime
                ];
            }
        }
        
        return $ret;
    }

    /**
     * イベントの作成
     * 基本的にタイトル、開始時間、終了時間を入れる
     *
     * @param array $data
     * @param GoogleUser $user
     * @return string
     */
    public function createEvent(array $data, GoogleUser $user): string
    {
        $event = $this->oauthCalendarService->createEvent($user, $data);
        return $event->id;
    }

    public function getHolidays(GoogleUser $user): array
    {
        return $this->oauthCalendarService->getHolidays($user);
    }

    /**
     * カレンダーから予定を削除する
     *
     * @param string $event_id
     * @param GoogleUser $user
     * @return mixed
     */
    public function deleteEvent($event_id, GoogleUser $user)
    {
        return $this->oauthCalendarService->deleteEvent($user, $event_id);
    }
}