<?php

use Collei\Http\Filters\FilterChain as Filters;

use App\Filters\AuthFilter;
use App\Filters\AuthLoaderFilter;
use App\Filters\CsrfPostRequestFilter;
use App\Filters\PlatStatsInterceptorFilter;
use App\Filters\AvailabilityInterceptorFilter;

Filters::add(CsrfPostRequestFilter::class);
Filters::add(PlatStatsInterceptorFilter::class);
Filters::add(AvailabilityInterceptorFilter::class);
Filters::add(AuthLoaderFilter::class);
Filters::add(AuthFilter::class);

