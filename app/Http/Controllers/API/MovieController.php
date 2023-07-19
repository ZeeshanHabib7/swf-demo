<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use App\Models\Movie;

class MovieController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $searchTerm = !empty($request->input('search')) ? $request->input('search') : '';


            // Check if movies are already cached
            if (Cache::has('movies')) {
                $movies = Cache::get('movies');
            } else {
                // Fetch Star Wars movies from SWAPI
                $movies = $this->fetchMovies();

                // Cache the movies for a specific duration
                Cache::put('movies', $movies, now()->addMinutes(2));
            }
            if ($searchTerm != '') {
                // Perform a search query based on the title column
                $movies = Movie::where('title', 'LIKE', '%' . $searchTerm . '%')->get();
            }
            return $this->successResponse($movies, 'Movies fetched successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            // Retrieve a specific movie from the database
            $movie = Movie::findOrFail($id);
            return $this->successResponse($movie, 'Movies fetched successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Update the movie in the database
            $movie = Movie::findOrFail($id);
            $movie->update($request->all());

            // Update the movie in the cache
            $cacheKey = 'movies'; // Update the cache key
            $movies = Cache::get($cacheKey);

            // Find the movie in the cached movies and update it
            $updatedMovieIndex = collect($movies)->search(function ($item) use ($id) {
                return $item->toArray()['id'] == $id;
            });

                if ($updatedMovieIndex !== false) {
                $movies[$updatedMovieIndex] = $movie->toArray();
            }

            // Store the updated movies back in the cache
            Cache::put($cacheKey, $movies, now()->addMinutes(60));

            return $this->successResponse($movie, 'Movie updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function destroy($id)
    {
        try {
            // Delete the movie from the database
            $movie = Movie::findOrFail($id);
            $movie->delete();

            // Delete the movie from the cache if it exists
            if (Cache::has('movies')) {
                $movies = Cache::get('movies');
                $movies = collect($movies)->reject(function ($item) use ($id) {
                    return $item['id'] == $id;
                });
                Cache::put('movies', $movies, now()->addMinutes(2));
            }

            return $this->successResponse($movie, 'Movie deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function fetchMovies()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://swapi.dev/api/films');
        $response2 = $client->request('GET', 'https://api.themoviedb.org/3/trending/movie/day', [
        'headers' => [
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJiOWRmOTNlY2E4ZWViOWVhNGM5NzMxZTQxOTIxYTgzNCIsInN1YiI6IjY0Yjc4YTNkMTA5Y2QwMDBhZTkyYjg3NSIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.5RhDiLt7yrEzNN2eNdBxLF06s-rLppojytFgHdwH1Qk',
            'accept' => 'application/json',
        ],
        ]);
        if ($response->getStatusCode() === 200 && $response2->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents());
            $data2 = json_decode($response2->getBody()->getContents());

            // Process and format the relevant movie information
            $movies = collect($data->results)->map(function ($movie) {
                return [
                    'title' => $movie->title,
                    'episode_id' => $movie->episode_id,
                    'release_date' => $movie->release_date,
                    'director' => $movie->director,
                    'producer' => $movie->producer,
                    'opening_crawl' => $movie->opening_crawl,
                ];
            });

            $movies2 = collect($data2->results)->map(function ($movie) {
                return [
                    'title' => $movie->title,
                    'poster_path' => $movie->poster_path,
                    'media_type' => $movie->media_type,
                    'release_date' => $movie->release_date,
                    'vote_average' => $movie->vote_average,
                    'vote_count' => $movie->vote_count,
                    'opening_crawl' => $movie->overview,
                ];
            });

            // Filter out existing movies by checking if the title already exists in the database
            $existingMovies = Movie::whereIn('title', $movies->pluck('title'))->get();
            $newMovies = $movies->reject(function ($movie) use ($existingMovies) {
                return $existingMovies->pluck('title')->contains($movie['title']);
            });

             // Filter out existing movies by checking if the title already exists in the database
             $existingMovies2 = Movie::whereIn('title', $movies2->pluck('title'))->get();
             $newMovies2 = $movies2->reject(function ($movie) use ($existingMovies2) {
                 return $existingMovies2->pluck('title')->contains($movie['title']);
             });

            // Store the new movies in the database
            if ($newMovies->isNotEmpty()) {
                Movie::insert($newMovies->toArray());
            }
             // Store the new movies in the database
             if ($newMovies2->isNotEmpty()) {
                Movie::insert($newMovies2->toArray());
            }
            $movies = Movie::all();
            return $movies;
        }

        return [];
    }
}
