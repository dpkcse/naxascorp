<?php
namespace App\Domain\Articles; use App\Models\Article; final class ArticleDependencyInspector { public function inspect(Article $article):array{$homepage=$article->homepageItems()->count();$incoming=$article->newQuery()->whereHas('relatedArticles',fn($q)=>$q->whereKey($article->id))->count();return ['blocked'=>$homepage+$incoming>0,'homepage'=>$homepage,'incoming_relations'=>$incoming];} }
