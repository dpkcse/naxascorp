<?php
namespace App\Http\Controllers; use App\Domain\Faqs\FaqViewData; use Illuminate\Contracts\View\View; use Illuminate\Http\Request; class PublicFaqController extends Controller { public function __invoke(Request $r,FaqViewData $d):View{return view('faqs.index',['faqs'=>$d->get(),'preview'=>false]+app(PublicSiteController::class)->sharedViewData($r));} }
