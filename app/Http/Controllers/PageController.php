<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
	public function index()
	{
		$pages = Page::all();
		if ($pages->count() > 0) {
			return response()->json([
				"status" => 200,
				"data" => $pages,
				"message" => "Get all pages successfully."
			], 200);
		} else {
			return response()->noContent();
		}
		return response()->json([
			"status" => 404,
			error_log("aaa"),
			"message" => "No records found."
		], 404);
	}

	public function store(Request $request)
	{
		$validator = Validator::make($request->all(), [
			"name" => "required",
			"author" => "required",
			"content" => "bail|required|max:5000",
		]);

		if ($validator->fails()) {
			return response()->json([
				"status" => 400,
				"errors" => $validator->messages()
			], 400);
		} else {
			$page = Page::create($request->all());
			if ($page) {
				return response()->json(
					[
						"status" => 201,
						"data" => $page,
						"message" => "Add new page successfully"
					],
					201
				);
			} else {
				return response()->json([
					"status" => 500,
					"message" => "Something went wrong!!"
				], 500);
			}
		}
	}

	public function delete($id)
	{
		$page = Page::find($id);
		if (!$page) {
			return response()->json([
				"status" => 404,
				"message" => "No record found."
			], 404);
		}
		$page->delete();
		return response()->json([
			"status" => 200,
			"message" => "Page was deleted successfully."
		], 200);
	}

	public function getOnePage($id)
	{
		$page = Page::find($id);
		if (!$page) {
			return response()->json([
				"status" => 404,
				"message" => "No record found."
			], 404);
		} else {
			return response()->json([
				"status" => 200,
				"data" => $page,
				"message" => "Post was found successfully."
			], 200);
		}
	}

	public function update(Request $request, $id)
	{
		$page = Page::find($id);
		if (!$page) {
			return response()->json(
				[
					"status" => 404,
					"message" => "No record found."
				],
				404
			);
		}
		$validator = null;
		$validator = Validator::make($request->all(), [
			"name" => "required",
			"author" => "required",
			"content" => "bail|required|max:5000",
		]);

		if ($validator->fails()) {
			return response()->json(
				[
					"status" => 400,
					'errors' => $validator->errors()
				],
				400
			);
		} else {
			$page->name = $request->name;
			$page->author = $request->author;
			$page->content = $request->content;
		}
		$page->save();
		if ($page) {
			return response()->json(
				[
					"status" => 200,
					"data" => $page,
					'message' => 'Page was updated successfully.'
				],
				200
			);
		} else {
			return response()->json(
				[
					"status" => 500,
					'message' => 'Error server'
				],
				500
			);
		}
	}
	/** Trả về Page View */
	// public function index()
	// {
	// 	$pages = Page::all();
	// 	return view('page.index', compact('pages'));
	// }

	// // CREATE NEW PAGE
	// public function create()
	// {
	// 	return view("page.create");
	// }
	// public function store(Request $request)
	// {
	// 	$request->validate([
	// 		"name" => "required",
	// 		"author" => "required",
	// 		"content" => "bail|required|max:5000",
	// 	], [
	// 		"max" => ":attribute tối đa :max ký tự.",
	// 		"required" => ":attribute bắt buộc nhập.",
	// 	]);
	// 	Page::create($request->all());
	// 	return redirect()->route("page.index")
	// 		->with("success", "Thêm mới thành công.");
	// }

	// // EDIT PAGE
	// public function edit(Page $page)
	// {
	// 	return view('page.edit', compact('page'));
	// }
	// public function update(Request $request, Page $page)
	// {
	// 	$request->validate([
	// 		"name" => "required",
	// 		"author" => "required",
	// 		"content" => "bail|required|max:5000",
	// 	], [
	// 		"max" => ":attribute tối đa :max ký tự.",
	// 		"required" => ":attribute bắt buộc nhập.",
	// 	]);
	// 	$page->update($request->all());
	// 	return redirect()->route('page.index')
	// 		->with('success', "Cập nhật mới thành công.");
	// }

	// // DELETE PAGE
	// public function delete($id)
	// {
	// 	$pages = Page::find($id);
	// 	$pages->delete();
	// 	return redirect()->route("page.index")
	// 		->with("success", "Xóa thành công.");
	// }
}
