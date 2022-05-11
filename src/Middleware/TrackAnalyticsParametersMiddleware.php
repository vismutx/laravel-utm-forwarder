<?php

namespace Spatie\AnalyticsTracker\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\AnalyticsTracker\AnalyticsBag;

class TrackAnalyticsParametersMiddleware
{
    protected AnalyticsBag $analyticsBag;

    public function __construct(AnalyticsBag $analyticsBag)
    {
        $this->analyticsBag = $analyticsBag;
    }

    public function handle(Request $request, Closure $next)
    {
        $this->analyticsBag->putFromRequest($request);
        $this->analyticsBag->cleanIntended();

        return $next($request);
    }
}
