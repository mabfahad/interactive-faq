<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://abfahad.me
 * @since      1.0.0
 *
 * @package    Ifaq
 * @subpackage Ifaq/admin/partials
 */
?>
<div class="ifaq-accordion">
    <div class="ifaq-accordion-item">
        <div class="ifaq-question">
            What is WordPress?
            <span class="ifaq-icon">&#9662;</span>
        </div>
        <div class="ifaq-answer">
            WordPress is an open-source content management system (CMS) used to build websites and blogs.
            <div class="ifaq-meta">
                Status: <span class="ifaq-status active">Active</span> |
                Created: 2025-05-20 14:32:00
            </div>
            <div class="ifaq-actions">
                <a href="#" class="edit">Edit</a>
                <a href="#" class="delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</a>
            </div>
        </div>
    </div>

    <div class="ifaq-accordion-item">
        <div class="ifaq-question">
            How do I install a plugin?
            <span class="ifaq-icon">&#9662;</span>
        </div>
        <div class="ifaq-answer">
            Go to the Plugins menu in your dashboard and click "Add New", then search or upload your plugin.
            <div class="ifaq-meta">
                Status: <span class="ifaq-status active">Active</span> |
                Created: 2025-05-21 09:15:00
            </div>
            <div class="ifaq-actions">
                <a href="#" class="edit">Edit</a>
                <a href="#" class="delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</a>
            </div>
        </div>
    </div>

    <div class="ifaq-accordion-item">
        <div class="ifaq-question">
            How can I change my site title?
            <span class="ifaq-icon">&#9662;</span>
        </div>
        <div class="ifaq-answer">
            Go to Settings → General and update the Site Title and Tagline fields.
            <div class="ifaq-meta">
                Status: <span class="ifaq-status deactive">Deactive</span> |
                Created: 2025-05-22 11:45:00
            </div>
            <div class="ifaq-actions">
                <a href="#" class="edit">Edit</a>
                <a href="#" class="delete" onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</a>
            </div>
        </div>
    </div>
</div>



