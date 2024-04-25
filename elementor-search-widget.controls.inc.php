<?php

namespace Elementor;

    // Fügen Sie hier eine neue Sektion hinzu
    $this->start_controls_section(
        'section_columns',
        [
            'label' => __('Columns', 'fylr-integration'),
        ]
    );

    // Fügen Sie hier Ihre Steuerelemente hinzu

    ////////////////////////////////////////////
    // SPALTEN
    ////////////////////////////////////////////

    // Anzahl der Spalten
    $this->add_control(
        'columns',
        [
            'label' => __('Columns', 'fylr-integration'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6'
            ],
            'default' => '2',
        ]
    );

    // Abstand zwischen den Spalten
    $this->add_control(
        'column_spacing_right',
        [
            'label' => __('Column Spacing Right', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 20,
                'unit' => 'px',
            ],
        ]
    );

    // Abstand zwischen den Spalten
    $this->add_control(
        'column_spacing_bottom',
        [
            'label' => __('Column Spacing Bottom', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 20,
                'unit' => 'px',
            ],
        ]
    );

    $this->end_controls_section();
    $this->start_controls_section(
        'section_title',
        [
            'label' => __('Title', 'fylr-integration'),
        ]
    );

    ////////////////////////////////////////////
    // TITEL
    ////////////////////////////////////////////

    // Option für die Anzeige des Titels
    $this->add_control(
        'show_title',
        [
            'label' => __('Show Title', 'fylr-integration'),
            'type' => Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'fylr-integration'),
            'label_off' => __('No', 'fylr-integration'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]
    );

    // Textfarbe
    $this->add_control(
        'title_color',
        [
            'label' => __('Text Color', 'fylr-integration'),
            'type' => Controls_Manager::COLOR,
            'default' => '#000000',
            'selectors' => [
                '{{WRAPPER}} .fylr-search-results .fylr-search-result .title' => 'color: {{VALUE}};',
            ],
        ]
    );

    // Textgröße für den Titel
    $this->add_control(
        'title_font_size',
        [
            'label' => __('Title Font Size', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 24,
                'unit' => 'px',
            ],
            'condition' => [
                'show_title' => 'yes',
            ],
        ]
    );

    $this->add_control(
        'title_line_height',
        [
            'label' => __('Title Line Height', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 1.5,
                'unit' => 'em',
            ],
            'condition' => [
                'show_title' => 'yes',
            ],
        ]
    );

    $this->add_control(
        'title_font_weight',
        [
            'label' => __('Title Font Weight', 'fylr-integration'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'normal' => __('Normal', 'fylr-integration'),
                'bold' => __('Bold', 'fylr-integration'),
                'bolder' => __('Bolder', 'fylr-integration'),
                'lighter' => __('Lighter', 'fylr-integration'),
                '100' => '100',
                '200' => '200',
                '300' => '300',
                '400' => '400',
                '500' => '500',
                '600' => '600',
                '700' => '700',
                '800' => '800',
                '900' => '900',
            ],
            'default' => 'normal',
            'condition' => [
                'show_title' => 'yes',
            ],
        ]
    );

    $this->end_controls_section();
    $this->start_controls_section(
        'section_content',
        [
            'label' => __('Content', 'fylr-integration'),
        ]
    );

    ////////////////////////////////////////////
    // INHALT
    ////////////////////////////////////////////

    // Option für die Anzeige des Inhalts
    $this->add_control(
        'show_content',
        [
            'label' => __('Show Content', 'fylr-integration'),
            'type' => Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'fylr-integration'),
            'label_off' => __('No', 'fylr-integration'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]
    );

    // Textfarbe
    $this->add_control(
        'content_color',
        [
            'label' => __('Content Color', 'fylr-integration'),
            'type' => Controls_Manager::COLOR,
            'default' => '#000000',
            'selectors' => [
                '{{WRAPPER}} .fylr-search-results .fylr-search-result .content' => 'color: {{VALUE}};',
            ],
        ]
    );

    // Textgröße für den Inhalts
    $this->add_control(
        'content_font_size',
        [
            'label' => __('Content Font Size', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 24,
                'unit' => 'px',
            ],
            'condition' => [
                'show_content' => 'yes',
            ],
        ]
    );

    $this->add_control(
        'content_line_height',
        [
            'label' => __('Content Line Height', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', '%'],
            'default' => [
                'size' => 1.5,
                'unit' => 'em',
            ],
            'condition' => [
                'show_content' => 'yes',
            ],
        ]
    );

    $this->add_control(
        'content_font_weight',
        [
            'label' => __('Content Font Weight', 'fylr-integration'),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'normal' => __('Normal', 'fylr-integration'),
                'bold' => __('Bold', 'fylr-integration'),
                'bolder' => __('Bolder', 'fylr-integration'),
                'lighter' => __('Lighter', 'fylr-integration'),
                '100' => '100',
                '200' => '200',
                '300' => '300',
                '400' => '400',
                '500' => '500',
                '600' => '600',
                '700' => '700',
                '800' => '800',
                '900' => '900',
            ],
            'default' => 'normal',
            'condition' => [
                'show_content' => 'yes',
            ],
        ]
    );

    // Anzahl Buchstaben Content
    $this->add_control(
        'content_char_max',
        [
            'label' => __('Max chars content', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['Count'],
            'range' => [
                'Count' => [
                    'min' => 1,
                    'max' => 200,
                ],
            ],
            'default' => [
                'unit' => 'Count',
                'size' => 200,
            ],
            'condition' => [
                'show_content' => 'yes',
            ],
        ]
    );


    $this->end_controls_section();
    $this->start_controls_section(
        'section_image',
        [
            'label' => __('Image', 'fylr-integration'),
        ]
    );

    ////////////////////////////////////////////
    // BILD
    ////////////////////////////////////////////

   // Option für die Anzeige des Bilds
    $this->add_control(
        'show_image',
        [
            'label' => __('Show Image', 'fylr-integration'),
            'type' => Controls_Manager::SWITCHER,
            'label_on' => __('Yes', 'fylr-integration'),
            'label_off' => __('No', 'fylr-integration'),
            'return_value' => 'yes',
            'default' => 'yes',
        ]
    );

    // Bildbreite in Prozent
    $this->add_control(
        'image_width_percentage',
        [
            'label' => __('Image Width Percentage', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['%'],
            'range' => [
                '%' => [
                    'min' => 1,
                    'max' => 100,
                ],
            ],
            'default' => [
                'unit' => '%',
                'size' => 100,
            ],
            'condition' => [
                'show_image' => 'yes',
            ],
        ]
    );

    // Bildbreite in Prozent
    $this->add_control(
        'image_border-radius',
        [
            'label' => __('Image Border Radius', 'fylr-integration'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => [
                '%' => [
                    'min' => 1,
                    'max' => 100,
                ],
            ],
            'default' => [
                'unit' => 'px',
                'size' => 100,
            ],
            'condition' => [
                'show_image' => 'yes',
            ],
        ]
    );

    // Schließen Sie die Sektion
    $this->end_controls_section();
?>