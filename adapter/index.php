<?php

class LocalNewsController extends Controller
{
    public function index(Request $request)
    {
        $locator = new IpLocation;
        $location = $locator->locate($request->ip());

        /*
         * Here we are being forced to adapt our domain logic to 3rd party package
         * Literally we're gluing ourselves to their implementation
         */
        $news = News::near($location)->take(5)->get();

        return NewsResource::collection($news);
    }
}
