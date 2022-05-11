<?php

namespace Spatie\AnalyticsTracker;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Spatie\AnalyticsTracker\Sources\RequestParameter;

class AnalyticsBag
{
    protected Session $session;

    /** @var array[]|string[][] */
    protected array $trackedParameters;

    protected string $sessionKey;

    public function __construct(Session $session, array $trackedParameters, string $sessionKey)
    {
        $this->session = $session;
        $this->trackedParameters = $trackedParameters;
        $this->sessionKey = $sessionKey;
    }

    public function putFromRequest(Request $request)
    {
        $parameters = $this->determineFromRequest($request);

        if(!count($parameters)){
            return;
        }

        $this->session->put($this->sessionKey, $parameters);
    }

    public function get(): array
    {
        return $this->session->get($this->sessionKey, []);
    }

    protected function determineFromRequest(Request $request): array
    {
        return collect($this->trackedParameters)
            ->mapWithKeys(function ($trackedParameter) use ($request) {
                $source = new $trackedParameter['source']($request);

                return [$trackedParameter['key'] => $source->get($trackedParameter['key'])];
            })
            ->filter()
            ->toArray();
    }

    public function cleanRequestQuery(Request $request)
    {
        foreach ($this->trackedParameters as $trackedParameter) {
            if($trackedParameter['source'] == RequestParameter::class){
                $request->request->remove($trackedParameter['key']);
            }
        }
    }

    public function cleanIntended()
    {
        $url = $this->session->get('url.intended');
        if(!$url){
            return;
        }
        $parameters = $this->get();
        if(!count($parameters)){
            return;
        }
        foreach ($parameters as $key => $item) {
            $url = str_replace($key . '=' . $item, '', $url);
        }
        $url = str_replace('&&', '', $url);
//        dd($url);

        $this->session->put('url.intended', $url);
    }
}
