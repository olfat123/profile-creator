<?php
namespace ProfileCreator\Form;

interface FormHandlerInterface {
    public function render_form(): string;
    public function process_form(): void;
}