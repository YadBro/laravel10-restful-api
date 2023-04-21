<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
  public function index()
  {
    $posts = Post::latest()->paginate(5);

    return new PostResource(true, 'List Data Posts', $posts);
  }

  public function store(Request $request)
  {
    $allRequest = $request->all();
    $validator = Validator::make($allRequest, [
      'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      'title' => 'required',
      'content' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $image = $request->file('image');
    $image->storeAs('public/posts', $image->hashName());

    $newPost = Post::create([
      'image' => $image->hashName(),
      'title' => $request->title,
      'content' => $request->content,
    ]);


    return new PostResource(true, 'Create post successfully', $newPost);
  }

  public function show(string $id)
  {
    $post = Post::find($id);

    return new PostResource(true, 'Detail data post!', $post);
  }

  public function update(Request $request, string $id)
  {
    $allRequest = $request->all();
    $validator = Validator::make($allRequest, [
      'title' => 'required',
      'content' => 'required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }

    $post = Post::find($id);

    if ($request->hasFile('image')) {

      $image = $request->file('image');
      $image->storeAs('public/posts', $image->hashName());

      Storage::delete('public/posts/' . basename($post->image));

      $post->update([
        'title' => $request->title,
        'content' => $request->content,
        'image' => $image->hashName(),
      ]);
    } else {
      $post->update([
        'title' => $request->title,
        'content' => $request->content,
      ]);
    }

    return new PostResource(true, 'Post success to update', $post);
  }

  public function destroy(string $id)
  {
    $post = Post::find($id);
    Storage::delete('public/posts/' . basename($post->image));


    $post->delete();

    return new PostResource(true, 'Deleted post successfully', null);
  }
}
