<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

use App\Post;
use App\Http\Requests\post\UpdatePostRequest;
use App\Http\Requests\post\CreatePostRequest;
use App\Tag;
use Facade\Ignition\Tabs\Tab;

class PostController extends Controller
{   
    public function __construct()
    {
        $this->middleware('verifyCategoryCount')->only(['create','store']);
        
    }
  
    public function index() 
    { 
        return view('posts.index')->with('posts',Post::all()); 
        //
    }
 
  
    public function create()

    {return view('posts.create')->with('categories',Category::all())->with('tags',Tag::all());
        //
    }

  
    public function store(CreatePostRequest $request){

        $image=$request->image->store('posts');
        $post= Post::create(['title'=>$request->title,
        'description'=>$request->description,
        'content'=>$request->content,
        'image'=>$image,
        'published_at'=>$request->published_at,
        'category_id'=>$request->category,
        'user_id'=>auth()->user()->id,
        ]);
        if($request->tags){
            $post->tags()->attach($request->tags);
        }
        session()->flash('success','Post created successfully');
       return redirect(route('posts.index'));
    
    }

   
    public function edit(Post $post)
    {
        
        return view('posts.create')->with('post',$post)->with('categories',Category::all())->with('tags',Tag::all());
        //
    }

   
    public function update(UpdatePostRequest $request, Post $post)
    { 
          $data=$request->only(['title','description','content','published_at']);

          if($request->hasFile('image')){
          $image= $request->image->store('posts');
        //   Storage::delete($post->image);
        $post->delete_image();
          $data['image']=$image;
          }

          if($request->tags){
              $post->tags()->sync($request->tags);
          }
          $post->update($data);
          session()->flash('success','Post Updated  Successfully');
          return redirect(route('posts.index'));

        //
    }

  
    public function destroy($id)
    {   $post=Post::withTrashed()->where('id',$id)->firstOrFail();
        
        if($post->trashed()){
            // Storage::delete($post->image); used in the Post model due to code refractoring 
            $post->delete_image();
            $post->forceDelete();
            session()->flash('success','Post deleted  permanently');
        } 
        else{
            $post->delete();
            session()->flash('success','Post trashed successfully');
        }
      
        return  redirect(route('posts.index'));
        //
    }

    public function treashed(){
        $trashed=Post::onlyTrashed()->get();
        return view('posts.index')->with('posts',$trashed);
    }
    public function restore($id){
        $post=Post::withTrashed()->where('id',$id)->firstOrFail();
        $post->restore();
        session()->flash('success','Post Restored successfully');
        return redirect()->back();

    }


    
}
