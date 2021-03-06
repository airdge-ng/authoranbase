<?php

namespace Tests\API;

use App\Models\Author;
use App\Models\Book;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class BookAPIControllerTest
 */
class BookAPIControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->signInWithDefaultAdminUser();
    }

    /** @test */
    public function test_can_get_featured_books()
    {
        $featuredBooks = factory(Book::class, 5)->create(['is_featured' => 1]);
        factory(Book::class, 5)->create(['is_featured' => 0]);

        $response = $this->getJson(route('api.books.index', ['is_featured' => true]));

        $this->assertSuccessMessageResponse($response, 'Books retrieved successfully.');
        $this->assertCount(5, $response->original['data']);
    }

    /** @test */
    public function test_get_count_of_total_books()
    {
        factory(Book::class, 5)->create();

        $response = $this->getJson(route('api.books.count'));

        $this->assertEquals(5, $response->original['data']);
    }

    /** @test */
    public function test_can_get_search_books_by_book_name_and_return_total_count()
    {
        $book = factory(Book::class)->create(['name' => 'MyBook First']);
        factory(Book::class)->create(['name' => 'ThirdEye']);

        $response = $this->getJson(route('api.books.index', ['search' => 'mybook', 'by_books' => 1]));

        $this->assertCount(1, $response->original['data']);
        $this->assertEquals($response->original['data'][0]['id'], $book->id);
        $this->assertEquals(1, $response->original['totalRecords']);
    }

    /** @test */
    public function test_can_get_search_books_by_author_name_and_return_total_count()
    {
        $book = factory(Book::class)->create(['name' => 'ThirdEye']);
        $author = factory(Author::class)->create(['first_name' => 'Jhon', 'last_name' => 'Doe']);
        DB::table('book_authors')->insert(['book_id' => $book->id, 'author_id' => $author->id]);
        factory(Book::class)->create(['name' => 'MyBook First']);

        $response = $this->getJson(route('api.books.index', ['search' => 'Jhon', 'by_authors' => 1]));

        $this->assertCount(1, $response->original['data']);
        $this->assertEquals($response->original['data'][0]['id'], $book->id);
    }
}