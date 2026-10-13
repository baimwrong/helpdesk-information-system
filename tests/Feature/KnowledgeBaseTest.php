<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_kb_portal_can_be_viewed(): void
    {
        $response = $this->get('/kb');
        $response->assertStatus(200);
        $response->assertSee('Knowledge Base');
    }

    public function test_kb_article_can_be_viewed_and_increments_views(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create([
            'name' => 'General Guide',
            'slug' => 'general-guide',
            'is_active' => true,
        ]);

        $article = KnowledgeBaseArticle::create([
            'title' => 'Sample Troubleshooting Guide',
            'slug' => 'sample-troubleshooting-guide',
            'category_id' => $category->id,
            'author_id' => $author->id,
            'content' => 'Step 1: Check power cable. Step 2: Restart system.',
            'is_published' => true,
            'views_count' => 5,
        ]);

        $response = $this->get('/kb/sample-troubleshooting-guide');
        $response->assertStatus(200);
        $response->assertSee('Sample Troubleshooting Guide');

        $article->refresh();
        $this->assertEquals(6, $article->views_count);
    }

    public function test_user_can_vote_article_helpfulness(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $article = KnowledgeBaseArticle::create([
            'title' => 'Vote Testing Guide',
            'slug' => 'vote-testing-guide',
            'author_id' => $author->id,
            'content' => 'Helpful steps...',
            'is_published' => true,
            'helpful_count' => 0,
        ]);

        $this->post("/kb/{$article->id}/vote", ['type' => 'helpful']);

        $article->refresh();
        $this->assertEquals(1, $article->helpful_count);
    }
}
