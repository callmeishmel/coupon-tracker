<?php

// Decision Logic Reports
Breadcrumbs::register('decision-logic-reports', function($breadcrumbs)
{
    $breadcrumbs->push('Reports', route('reports.decision-logic.view'));
});

// Decision Logic Reports > [Report]
Breadcrumbs::register('decision-logic-report', function($breadcrumbs, $report)
{
    $breadcrumbs->parent('decision-logic-reports');
    $breadcrumbs->push($report->dl_request_code, route('transactions.decision-logic.view', $report->id));
});

// Decision Logic Reports > Vendor Verification
Breadcrumbs::register('decision-logic-vendor-verification', function($breadcrumbs)
{
    $breadcrumbs->parent('decision-logic-reports');
    $breadcrumbs->push('Vendor Verification', route('vendor-verification.decision-logic.view'));
});

// Decision Logic Reports > Vendor Verification > Vendor Tagging
Breadcrumbs::register('vendor-tagging', function($breadcrumbs)
{
    $breadcrumbs->parent('decision-logic-vendor-verification');
    $breadcrumbs->push('Vendor Tagging', route('index.vendor-tagging.view'));
});


// FRONTEND (Customers)

// Customer Dashboard
Breadcrumbs::register('customer-dashboard', function($breadcrumbs)
{
    $breadcrumbs->push('Dashboard', route('index.frontend.get'));
});

// Customer Dashboard > Bank Account
Breadcrumbs::register('customer-financial-account', function($breadcrumbs)
{
    $breadcrumbs->parent('customer-dashboard');
    $breadcrumbs->push('Financial Account', route('index.financial-account.get'));
});

// Customer Dashboard > Bank Account > [Report]
Breadcrumbs::register('customer-report-details', function($breadcrumbs, $report)
{
    $breadcrumbs->parent('customer-financial-account');
    $breadcrumbs->push($report->dl_request_code, route('view-transactions.financial-account.view', $report->id));
});
