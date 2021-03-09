<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\PostCategory;
use DB;

class PostCategoryController extends Controller
{
    public function save(Request $request)
    {
        if ($request->category_id) { //edit
            $rules = array(
                'category_id' => 'integer|exists:post_categories,id',
                'name' => 'required|unique:post_categories,id,' . $request->category_id,
            );
        } else {
            $rules = array(
                'name' => 'required|unique:post_categories',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->category_id) {
                $category = PostCategory::find($request->category_id);
                if (!$category) {
                    return self::send_bad_request_response('Incorrect post category id. Please check and try again!');
                }
                $category->updated_by = auth()->user()->id;
            } else {
                $category = new PostCategory();
                $category->created_by = auth()->user()->id;
            }

            $category->name = $request->name;
            $category->save();

            DB::commit();
            return self::send_success_response([], 'Post Category Stored Sucessfully');

        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function getList(Request $request)
    {
        $rules = array(
            'count_per_page' => 'nullable|numeric',
            'order_by' => 'nullable|in:desc,asc',
        );
        $valid = self::customValidation($request, $rules);
        if ($valid) {return $valid;}

    try {
        $paginate = $request->count_per_page ? $request->count_per_page : 10;
        $order_by = $request->order_by ? $request->order_by : 'desc';
        $pageNumber = $request->page ? $request->page : 1;

            $data = PostCategory::orderBy('name', $order_by);
            if($request->withtrash){
                $data = $data->withTrashed();
            }
            $paginatedata = $data->paginate($paginate, ['*'], 'page', $pageNumber);
            
            $list = collect();
            $data->each(function ($category) use (&$list) {
                $list->push($category->getData());
            });

            $result['list'] = $list;
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();
            
            return self::send_success_response($result, 'Post Category content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\PostCategory', $request->id);
    }
}
