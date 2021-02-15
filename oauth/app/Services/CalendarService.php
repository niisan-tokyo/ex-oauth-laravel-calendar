<?php
namespace App\Services;

use App\Models\GoogleUser;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class CalendarService
{
    private Google_Client $client;

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
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
        $this->setAccessToken($user);
        $service = new Google_Service_Calendar($this->client);
        $events = $service->events->listEvents('primary', [
            'timeMin' => Carbon::now()->subDays(7)->format(DATE_RFC3339),
            'timeMax' => Carbon::now()->addDays(7)->format(DATE_RFC3339)
        ]);
        $ret = [];

        while(true) {
            foreach ($events->getItems() as $event) {
                if ($event->start and $event->end) {
                    $ret[] = [
                        'id' => $event->id,
                        'summary' => $event->getSummary(),
                        'start' => $event->start->dateTime,
                        'end' => $event->end->dateTime
                    ];
                }
            }
            $pageToken = $events->getNextPageToken();
            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $events = $service->events->listEvents('primary', $optParams);
            } else {
                break;
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
        $event = new Google_Service_Calendar_Event([
            'summary' => $data['summary'],
            'start' => [
                'dateTime' => Carbon::parse($data['start'])->format(DATE_RFC3339)
            ],
            'end' => [
                'dateTime' => Carbon::parse($data['end'])->format(DATE_RFC3339)
            ]
        ]);
        $this->setAccessToken($user);
        $service = new Google_Service_Calendar($this->client);
        $new_event = $service->events->insert('primary', $event);
        return $new_event->id;
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
        $this->setAccessToken($user);
        $service = new Google_Service_Calendar($this->client);
        return $service->events->delete('primary', $event_id);
    }

    /**
     * アクセストークンのセット
     * 
     * @param GoogleUser $user
     * 
     * @return void
     */
    private function setAccessToken(GoogleUser $user):void
    {
        if (Carbon::now()->timestamp >= $user->expires - 30) {
            $token = $this->client->fetchAccessTokenWithRefreshToken($user->refresh_token);
            $user->access_token = $token['access_token'];
            $user->expires = Carbon::now()->timestamp + $token['expires_in'];
            $user->save();
        }

        $this->client->setAccessToken($user->access_token);
    }
}