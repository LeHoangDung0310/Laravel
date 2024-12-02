<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LocalizationController extends Controller
{
    function adminIndex() : View {
        $languages = Language::all();
        return view('admin.localization.admin-index', compact('languages'));
    }

    function frontnedIndex() : View {
        $languages = Language::all();
        return view('admin.localization.frontend-index', compact('languages'));
    }


    function extractLocalizationStrings(Request $request)
    {
        $directory = $request->directory;
        $languageCode = $request->language_code;
        $fileName = $request->file_name;


        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        $localizationStrings = [];

        // Iterate over each file in the directory
        foreach($files as $file){
            if($file->isDir()){
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            preg_match_all('/__\([\'"](.+?)[\'"]\)/', $contents, $matches);

            if(!empty($matches[1])){
                foreach($matches[1] as $match){
                    $localizationStrings[$match] = $match;
                }
            }

        }

        $phpArray = "<?php\n\nreturn " . var_export($localizationStrings, true) . ";\n";

        // create language sub folder if it is not exit
        if(!File::isDirectory(lang_path($languageCode))){
            File::makeDirectory(lang_path($languageCode), 0755, true);
        }

        // dd(lang_path($languageCode.'/'.$fileName.'.php'));
        file_put_contents(lang_path($languageCode.'/'.$fileName.'.php'), $phpArray);

    }


    function updateLangString(Request $request) : RedirectResponse {
        $languageStrings = trans($request->file_name, [], $request->lang_code);

        $languageStrings[$request->key] = $request->value;

        $phpArray = "<?php\n\nreturn " . var_export($languageStrings, true) . ";\n";

        file_put_contents(lang_path($request->lang_code.'/'.$request->file_name.'.php'), $phpArray);

        toast(__('Updated Successfully!'), 'success');

        return redirect()->back();

    }

    function translateString(Request $request)
    {
        try {
            $langCode = $request->language_code;
            $languageStrings = trans($request->file_name, [], $request->language_code);
            $keyStrings = array_keys($languageStrings);

            // Khởi tạo mảng kết quả dịch
            $translatedValues = [];

            // Chia nhỏ mảng thành các chunks, mỗi chunk có tổng độ dài < 999 ký tự
            $currentChunk = [];
            $currentLength = 0;
            $chunks = [];

            foreach ($languageStrings as $key => $text) {
                $textLength = strlen($text);

                if ($currentLength + $textLength > 900) { // để buffer 99 ký tự
                    $chunks[] = $currentChunk;
                    $currentChunk = [];
                    $currentLength = 0;
                }

                $currentChunk[] = [
                    "Text" => $text,
                    "originalKey" => $key
                ];
                $currentLength += $textLength;
            }

            // Thêm chunk cuối cùng nếu còn
            if (!empty($currentChunk)) {
                $chunks[] = $currentChunk;
            }

            // Xử lý từng chunk
            foreach ($chunks as $chunk) {
                $textsToTranslate = array_map(function ($item) {
                    return ["Text" => $item["Text"]];
                }, $chunk);

                $response = Http::withOptions([
                    'verify' => false,
                ])->withHeaders([
                    'x-rapidapi-host' => 'microsoft-translator-text-api3.p.rapidapi.com',
                    'x-rapidapi-key' => 'ca17318d92msh7f1bb4f8a7ef739p136c82jsn99d73ec817eb',
                    'Content-Type' => 'application/json',
                ])->post("https://microsoft-translator-text-api3.p.rapidapi.com/translate?to={$langCode}&api-version=3.0", $textsToTranslate);

                if (!$response->successful()) {
                    throw new \Exception('Translation API request failed: ' . $response->body());
                }

                $translations = $response->json();

                // Ghép kết quả dịch với key gốc
                foreach ($translations as $index => $translation) {
                    $originalKey = $chunk[$index]['originalKey'];
                    $translatedValues[$originalKey] = $translation['translations'][0]['text'] ?? '';
                }

                // Delay nhỏ giữa các request để tránh rate limit
                usleep(100000); // 100ms
            }

            // Kiểm tra số lượng phần tử
            if (count($keyStrings) !== count($translatedValues)) {
                throw new \Exception(sprintf(
                    "Translation mismatch: Expected %d items but got %d",
                    count($keyStrings),
                    count($translatedValues)
                ));
            }

            // Format PHP array string
            $phpArray = "<?php\n\nreturn array (\n";
            foreach ($translatedValues as $key => $value) {
                $phpArray .= "  '" . addslashes($key) . "' => '" . addslashes($value) . "',\n";
            }
            $phpArray .= ");\n";

            file_put_contents(lang_path($langCode . '/' . $request->file_name . '.php'), $phpArray);

            return response([
                'status' => 'success',
                'message' => __('admin.Translation is completed')
            ]);
        } catch (\Throwable $th) {
            \Log::error('Translation failed:', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }



}