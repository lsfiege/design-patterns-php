<?php
interface Locator
{
    public function fromIp(string $ipAddress): Mark;
}

class IpLocationLocator implements Locator
{
    public function fromIp(string $ipAddress): Mark
    {
        $locator = new IpLocation;
        $location = $locator->locate($ipAddress);

        return new Mark(
            $location['country_name'],
            $location['region_name'],
            $location['city']
        );
    }
}

class IpDatabaseLocator implements Locator
{
    public function fromIp(string $ipAddress): Mark
    {
        $locator = new IpDatabase;
        $location = $locator->findByIpAddress($ipAddress);

        return new Mark(
            $location['country'],
            $location['state_or_province'],
            $location['city_name']
        );
    }
}

class AppServiceProvider extends ServiceProvider
{
    // ...

    public function register()
    {
        $this->app->singleton(Locator::class, function ($app) {
            switch ($app->make('config')->get('services.ip-locator')) {
                case 'api':
                    return new IpLocationLocator;
                case 'database':
                    return new IpDatabaseLocator;
                default:
                    throw new \RuntimeException('Unknown IP Locator service');
            }
        });
    }
}

class LocalNewsController extends Controller
{
    public function index(Request $request, Locator $locator)
    {
        $mark = $locator->fromIp($request->ip());

        $news = News::near($mark)->take(5)->get();

        return NewsResource::collection($news);
    }
}

// In our tests
class LocalNewsTest extends TestCase
{
    /** @test */
    public function it_returns_only_local_news()
    {
        $response = $this->getJson('/api/local-news');

        $response->assertStatus(Response::HTTP_OK);
    }
}

// This test can be reaching out over the network, so let's fix that

class LocalNewsTest extends TestCase
{
    /** @test */
    public function it_returns_only_local_news()
    {
        $this->app->instance(
            Locator::class,
            FakeLocator::returning(new Mark('Canada', 'Ontario', 'Guelph'));
        );

        $response = $this->getJson('/api/local-news');

        $response->assertStatus(Response::HTTP_OK);
    }
}

class FakeLocator implements Locator
{
    private $mark;

    public function __construct(Mark $mark)
    {
        $this->mark = $mark;
    }

    public static function returning(Mark $mark)
    {
        return new static($mark);
    }

    public function fromIp(string $ipAddress): Mark
    {
        return $this->mark;
    }
}