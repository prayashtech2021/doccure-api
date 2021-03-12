<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\PostCategory;
use App\PostSubCategory;
use DB;

class PostSubCategoryController extends Controller
{
    public function save(Request $request)
    {
        if ($request->sub_category_id) { //edit
            $rules = array(
                'sub_category_id' => 'integer|exists:post_sub_categories,id',
                'category_id' => 'integer|exists:post_categories,id',
                'name' => 'required|unique:post_sub_categories,id,' . $request->sub_category_id,
            );
        } else {
            $rules = array(
                'category_id' => 'integer|exists:post_categories,id',
                'name' => 'required|unique:post_sub_categories',
            );
        }
        $valid = self::customValidation($request, $rules);
        if($valid){ return $valid;}

        try {
            DB::beginTransaction();
            if ($request->sub_category_id) {
                $category = PostSubCategory::find($request->sub_category_id);
                if (!$category) {
                    return self::send_bad_request_response('Incorrect post Sub Category id. Please check and try again!');
                }
                $category->updated_by = auth()->user()->id;
            } else {
                $category = new PostSubCategory();
                $category->created_by = auth()->user()->id;
            }
            $category->post_category_id = $request->category_id;
            $category->name = $request->name;
            $category->save();

            DB::commit();
            return self::send_success_response([], 'Post Sub Category Stored Sucessfully');

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

            $data = PostSubCategory::orderBy('name', $order_by);
            if($request->withtrash){
                $data = $data->withTrashed();
            }
            $paginatedata = $data->paginate($paginate, ['*'], 'page', $pageNumber);
            
            $list = collect();
            $paginatedata->getCollection()->each(function ($category) use (&$list) {
                $list->push($category->getData());
            });

            $result['list'] = $list;
            $result['total_count'] = $paginatedata->total();
            $result['last_page'] = $paginatedata->lastPage();
            $result['current_page'] = $paginatedata->currentPage();
            
            return self::send_success_response($result, 'Post Sub Category content fetched successfully');
        } catch (Exception | Throwable $e) {
            DB::rollback();
            return self::send_exception_response($exception->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        return self::customDelete('\App\PostSubCategory', $request->id);
    }
}
