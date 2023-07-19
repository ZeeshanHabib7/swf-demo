<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MovieTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test fetching movies without search term.
     *
     * @return void
     */
    public function testFetchingMoviesWithoutSearchTerm()
    {

        $token = $this->getUserToken();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token . '', 'Accept' => 'application/json'])->getJson('/api/movies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);
    }

    /**
     * Test fetching movies with search term.
     *
     * @return void
     */
    public function testFetchingMoviesWithSearchTerm()
    {
        $token = $this->getUserToken();

        $searchTerm = 'Movie 1';


        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token . '', 'Accept' => 'application/json'])->getJson('/api/movies?search=' . urlencode($searchTerm));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);
    }

    /**
     * Test retrieving a specific movie.
     *
     * @return void
     */
    public function testRetrievingSpecificMovie()
    {
        $token = $this->getUserToken();

        $movie =  $this->creatMoive();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token . '', 'Accept' => 'application/json'])->getJson('/api/movies/' . $movie->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);
    }

    /**
     * Test updating a movie.
     *
     * @return void
     */
    public function testUpdatingMovie()
    {
        $movie =  $this->creatMoive();

        $updatedData = [
            'title' => 'Updated Movie',
            'director' => 'Updated Director',
            'producer' => 'Updated Producer',
        ];

        $response = $this->putJson('/api/movies/' . $movie->id, $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('movies', $updatedData);
    }

    /**
     * Test deleting a movie.
     *
     * @return void
     */
    public function testDeletingMovie()
    {
        $movie = $this->creatMoive();
        $response = $this->deleteJson('/api/movies/' . $movie->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);

    }

    public function creatMoive(){
        $movie = Movie::create([
            'title' => 'Movie 1',
            'episode_id' => 1,
            'release_date' => '2022-01-01',
            'director' => 'Director 1',
            'producer' => 'Producer 1',
            'opening_crawl' => 'Opening crawl 1',
        ]);
        return $movie;
    }
}
