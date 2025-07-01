<?php

// use App\Models\User;
// use Laraveltoolkit\Facades\Sitemap;
//
// Sitemap::addUrl(route('login'));
// Sitemap::addUrl(route('logout'));
// Sitemap::addUrl(route('index'));
// Sitemap::fromQuery(User::query(), function (User $user) {
//    Sitemap::addUrl(route('index.user', $user->id), $user->created_at);
// });
// // OR
// //Sitemap::addIndex('users');
//
//
// Sitemap::domain('abc.dev.test', function () {
//    Sitemap::addUrl(route('login'));
// });
//
// Sitemap::index('users', function () {
//    Sitemap::fromCollection(collect([1,2,3]), function (int $number) {
//        Sitemap::addUrl(route('index', ['number' => $number, 'number2' => $number]));
//    });
//    Sitemap::fromQuery(User::query(), function (User $user) {
//        Sitemap::addUrl(route('index.user', $user->id), $user->created_at);
//    });
// });
