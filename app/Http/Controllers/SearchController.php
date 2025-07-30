<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Page;
use App\Models\FAQ;

class SearchController extends Controller
{
    /**
     * Recursively convert all array keys to camelCase.
     * Used to format API responses to camelCase keys.
     *
     * @param array $array
     * @return array
     */
    private function arrayKeysToCamelCase($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        $converted = [];
        foreach ($array as $key => $value) {
            $camelKey = is_string($key) ? lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)))) : $key;
            $converted[$camelKey] = is_array($value) ? $this->arrayKeysToCamelCase($value) : $value;
        }
        return $converted;
    }

    /**
     * Unified search endpoint for BlogPost, Product, Page, and FAQ models.
     * Returns paginated, sorted results for a query string.
     * GET /search?q=term
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $service = app(\App\Services\SearchService::class);
        $result = $service->unifiedSearch($query, $perPage, $page);
        return response()->json($this->arrayKeysToCamelCase($result), isset($result['meta']['message']) ? 400 : 200);
    
        $query = $request->input('q');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        if (!$query) {
            return response()->json(['data' => [], 'meta' => ['message' => 'No query provided']], 400);
        }

        $results = collect();

        // Blog Posts
        $blogPosts = BlogPost::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'blog_post',
                'title' => $item->title,
                'snippet' => Str::limit($item->body, 100),
                'link' => url('/blog/' . $item->id),
                'published_at' => $item->published_at,
            ];
        });
        $results = $results->concat($blogPosts);

        // Products
        $products = Product::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'product',
                'title' => $item->name,
                'snippet' => Str::limit($item->description, 100),
                'link' => url('/products/' . $item->id),
                'price' => $item->price,
            ];
        });
        $results = $results->concat($products);

        // Pages
        $pages = Page::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'page',
                'title' => $item->title,
                'snippet' => Str::limit($item->content, 100),
                'link' => url('/pages/' . $item->id),
            ];
        });
        $results = $results->concat($pages);

        // FAQs
        $faqs = FAQ::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'faq',
                'title' => $item->question,
                'snippet' => Str::limit($item->answer, 100),
                'link' => url('/faqs/' . $item->id),
            ];
        });
        $results = $results->concat($faqs);

        // Sort by recency if available (published_at or created_at)
        $results = $results->sortByDesc(function ($item) {
            return $item['published_at'] ?? $item['created_at'] ?? null;
        })->values();

        // Pagination
        $total = $results->count();
        $paginated = $results->forPage($page, $perPage)->values();

        // Optionally log the search query
        // Log::info('search', ['query' => $query, 'user_id' => optional(auth()->user())->id]);

        return response()->json($this->arrayKeysToCamelCase([
            'data' => $paginated,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'query' => $query,
            ],
        ]));
    }

    /**
     * Return typeahead suggestions for search queries from BlogPost, Product, Page, and FAQ models.
     * GET /search/suggestions?q=term
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        $query = $request->input('q');
        $service = app(\App\Services\SearchService::class);
        $result = $service->suggestions($query);
        return response()->json($result);
    
        // Optional: implement typeahead suggestions
        return response()->json(['suggestions' => []]);
    }

    /**
     * Return the latest search query logs from the search_logs table.
     * GET /search/logs
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs(Request $request)
    {
        $service = app(\App\Services\SearchService::class);
        $result = $service->logs();
        return response()->json($result);
    
        // Optional: implement search logs endpoint
        return response()->json(['logs' => []]);
    }

    /**
     * Trigger manual reindexing of search data.
     * POST /search/reindex
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reindex(Request $request)
    {
        $service = app(\App\Services\SearchService::class);
        $result = $service->reindex();
        return response()->json($result);
    
        // Optional: implement manual reindexing
        return response()->json(['message' => 'Reindex triggered']);
    }
}
