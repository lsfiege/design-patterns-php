<?php

class LocalNewsController extends Controller
{
    public function index(Request $request)
    {
        $locator = new IpLocation;
        $location = $locator->locate($request->ip());

        $mark = new Mark(
            $location['country_name'],
            $location['region_name'],
            $location['city']
        );

        $news = News::near($mark)->take(5)->get();

        return NewsResource::collection($news);
    }
}

/*
 * What happens if we need to switch the locator implementation?
 * For example, getting the location using IP locator package is expensive,
 * so the team buy a IP Database dataset
 */

 class LocalNewsController extends Controller
 {
     public function index(Request $request)
     {
         $locator = new IpDatabase;
         $location = $locator->findByIpAddress($request->ip());

         $mark = new Mark(
             $location['country'],
             $location['state_or_province'],
             $location['city_name']
         );

         $news = News::near($mark)->take(5)->get();

         return NewsResource::collection($news);
     }
 }

 /*
  * Here we detect that we can use Adapter Pattern to isolate our domain logic from outside influence
  */
