<?php

return [
    // Global spacing between rows in grid-based layouts (layout2/layout3/layout4).
    // Set via .env: PHOTO_ROW_GAP_RATIO=0.010 (recommended range 0.000 - 0.060)
    'row_gap_ratio' => (float) env('PHOTO_ROW_GAP_RATIO', 0.012),
];
