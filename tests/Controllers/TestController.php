<?php

namespace Leopard\Core\Tests\Controllers;

use Leopard\Core\Attributes\Route;

class TestController
{
    #[Route('/test', method: 'GET')]
    public function test(): string
    {
        return "Hello from TestController::test";
    }

    #[Route('/test/data', method: 'POST')]
    public function postData(): string
    {
        return "Data received in TestController::postData";
    }

    #[Route('/test/put', method: 'PUT')]
    public function testPut(): string
    {
        return "Hello from TestController::testPut";
    }

    #[Route('/test/delete', method: 'DELETE')]
    public function testDelete(): string
    {
        return "Hello from TestController::testDelete";
    }

    #[Route('/test/options', method: 'OPTIONS')]
    public function testOptions(): string
    {
        return "Hello from TestController::testOptions";
    }

    #[Route('/test/head', method: 'HEAD')]
    public function testHead(): string
    {
        return "Hello from TestController::testHead";
    }

    #[Route('/test/patch', method: 'PATCH')]
    public function testPatch(): string
    {
        return "Hello from TestController::testPatch";
    }

    #[Route('/user/{id}', method: 'GET')]
    public function getUser(string $id): string
    {
        return "User ID: $id";
    }

    #[Route('/post/{postId}/comment/{commentId}', method: 'GET')]
    public function getPostComment(string $postId, string $commentId): string
    {
        return "Post ID: $postId, Comment ID: $commentId";
    }

    #[Route('/product/{category}/{productId}', method: 'GET')]
    public function getProduct(string $category, string $productId): string
    {
        return "Category: $category, Product ID: $productId";
    }
}
