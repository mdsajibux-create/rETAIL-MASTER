<?php

namespace Modules\Catalog\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\SliderRequest;
use App\Http\Resources\Admin\AdminSliderResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\AllSliderManageInterface;
use Illuminate\Http\Request;

class SliderManageController extends Controller
{
    public function __construct(protected AllSliderManageInterface $sliderRepo)
    {
    }

    public function listSliders(Request $request)
    {
        $sliders = $this->sliderRepo->getPaginatedSlider(
            $request->per_page ?? 10,
            $request->language ?? 'en',
            $request->search ?? "",
            $request->sortField ?? 'id',
            $request->sort ?? 'asc',
            $request->platform ?? ""
        );
        return response()->json([
            'data' => AdminSliderResource::collection($sliders),
            'meta' => new PaginationResource($sliders)
        ]);
    }

    public function createSlider(SliderRequest $request)
    {
        $slider = $this->sliderRepo->store($request->all());
        createOrUpdateTranslation($request, $slider, 'App\Models\Slider', $this->sliderRepo->translationKeys());
        if ($slider) {
            return $this->success(translate('messages.save_success', ['name' => 'Slider Details']));
        } else {
            return $this->failed(translate('messages.save_failed', ['name' => 'Slider Details']));
        }
    }

    public function updateSlider(SliderRequest $request)
    {
        $slider = $this->sliderRepo->update($request->all());
        createOrUpdateTranslation($request, $slider, 'App\Models\Slider', $this->sliderRepo->translationKeys());
        if ($slider) {
            return $this->success(translate('messages.update_success', ['name' => 'Slider Details']));
        } else {
            return $this->failed(translate('messages.update_failed', ['name' => 'Slider Details']));
        }
    }

    public function getSliderById(Request $request)
    {
        return $this->sliderRepo->getSliderById($request->id);
    }

    public function changeSliderStatus(Request $request)
    {
        return $this->sliderRepo->changeStatus($request->id);
    }

    public function deleteSlider($id)
    {
        $this->sliderRepo->delete($id);
        return $this->success(translate('messages.delete_success', ['name' => 'Slider Details']));
    }
}
