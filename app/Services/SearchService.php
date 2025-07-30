<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Support\Str;

class SearchService
{
    public function unifiedSearch(string $query, int $perPage = 10, int $page = 1): array
    {
        $results = collect();

        // Blog Posts
        $blogPosts = BlogPost::search($query)->take(20)->get()->map(function ($item) {
            return [
                'type' => 'blog_post',
                'title' => $item->title,
                'snippet' => Str::limit($item->body, 100),
                'link' => url('/blog/' . $item->id),
                'created_at' => $item->created_at,
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
                'created_at' => $item->created_at,
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
                'created_at' => $item->created_at,
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
                'created_at' => $item->created_at,
            ];
        });
        $results = $results->concat($faqs);

        // Sort by recency
        $results = $results->sortByDesc(function ($item) {
            return $item['published_at'] ?? $item['created_at'] ?? null;
        })->values();

        // Pagination
        $total = $results->count();
        $paginated = $results->forPage($page, $perPage)->values();

        return [
            'data' => $paginated,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'query' => $query,
            ],
        ];
    }

    public function suggestions(string $query): array
    {
        // Implement typeahead logic if needed
        return ['suggestions' => []];
    }

    public function logs(): array
    {
        // Implement logs logic if needed
        return ['logs' => []];
    }

    public function reindex(): array
    {
        // Implement reindex logic if needed
        return ['message' => 'Reindex triggered'];
    }
}
