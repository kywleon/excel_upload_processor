<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Upload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;

class ProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function handle(): void
    {
        $this->upload->update(['status' => 'processing']);

        $path = storage_path('app/public/uploads/' . $this->upload->file_name);

        try {
            // Read file content
            $content = file_get_contents($path);

            // Remove BOM (if exists)
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }

            // Clean non-UTF-8 characters
            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            }

            // Temporarily save the cleaned content for League CSV parsing
            $tempPath = storage_path('app/tmp_clean_' . $this->upload->id . '.csv');
            file_put_contents($tempPath, $content);

            // Read CSV
            $csv = Reader::createFromPath($tempPath, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv as $row) {
                Product::updateOrCreate(
                    ['unique_key' => $row['UNIQUE_KEY']],
                    [
                        'product_title' => $row['PRODUCT_TITLE'],
                        'product_description' => $row['PRODUCT_DESCRIPTION'],
                        'style' => $row['STYLE#'],
                        'sanmar_mainframe_color' => $row['SANMAR_MAINFRAME_COLOR'],
                        'size' => $row['SIZE'],
                        'color_name' => $row['COLOR_NAME'],
                        'piece_price' => $row['PIECE_PRICE'],
                    ]
                );
            }

            // Update status
            $this->upload->update(['status' => 'completed']);

            // Clean up temporary file
            @unlink($tempPath);
        } catch (\Exception $e) {
            Log::error('Product import failed: ' . $e->getMessage());
            $this->upload->update(['status' => 'failed']);
        }
    }
}
