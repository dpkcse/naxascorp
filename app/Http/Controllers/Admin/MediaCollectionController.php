<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\MediaCollection; use Illuminate\Http\{RedirectResponse,Request}; use Illuminate\Support\Str; use Illuminate\Validation\Rule;
class MediaCollectionController extends Controller
{
 public function store(Request $request):RedirectResponse{$data=$request->validate(['name'=>['required','string','max:120'],'description'=>['nullable','string','max:1000']]);$data['slug']=Str::slug($data['name']);validator($data,['slug'=>['required','max:120',Rule::unique('media_collections','slug')]])->validate();MediaCollection::query()->create($data);return back()->with('status','Collection created.');}
 public function update(Request $request,MediaCollection $collection):RedirectResponse{$collection->update($request->validate(['name'=>['required','string','max:120'],'description'=>['nullable','string','max:1000'],'display_order'=>['required','integer','between:1,1000']]));return back()->with('status','Collection updated.');}
 public function destroy(MediaCollection $collection):RedirectResponse{abort_if($collection->assets()->exists(),409,'Move media out of this collection before deleting it.');$collection->delete();return back()->with('status','Empty collection deleted.');}
}
