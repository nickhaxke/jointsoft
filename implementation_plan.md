# Implementation Plan: Remove "Office Expenses" & Automate 3% Receipt Reward

## Goal
Based on your feedback, all office needs (like internet, rent, etc.) are funded directly through member contributions (Michango). Therefore, the separate "Office Expenses" tracking feature is unnecessary and will be completely removed.

At the same time, we will implement the improvement we discussed earlier: **When a member makes a sale and provides a TRA receipt, the 3% (30k) calculated is a REWARD from the office, and this amount will be automatically credited (paid) towards the member's outstanding Campaign Debt.**

## User Review Required
> [!IMPORTANT]  
> Tafadhali kagua mabadiliko yafuatayo kama ndiyo haswa unayoyataka:
> 1. Kufuta kipengele chote cha "Office Expenses" (hakitaonekana tena kwenye mfumo).
> 2. Kuboresha "Contributions" ili risiti yoyote itakayopitishwa na Admin, ile 3% iwe inakwenda moja kwa moja **kupunguza deni la mchango** la mwanachama husika.
>
> Bofya **"Proceed"** ili nianze kufanya mabadiliko haya mara moja!

## Proposed Changes

### 1. Remove "Office Expenses" Feature
- **`views/partials/sidebar.php`**:
  - [DELETE] The "Office Expenses" navigation link from the sidebar.
- **`public/index.php`**:
  - [DELETE] All routing related to `/expenses`.
- **`app/Controllers/DashboardController.php` & `views/dashboard/index.php`**:
  - [DELETE] The "Pending Expenses" summary cards and backend logic.

### 2. Improve "Contributions" (3% Reward Automation)
- **`app/Controllers/ReviewController.php`**:
  - [MODIFY] The `approve` method logic.
  - When approving a sale, calculate the 3% reward.
  - Instead of creating a `contribution_due` debt, the system will:
    1. Search for the user's active/pending Campaign in the `member_contributions` table.
    2. Add the 3% amount to the `paid_amount` of that campaign, effectively reducing their outstanding balance.
    3. Record this in the `ledgers` table as a `receipt_commission` (Credit), with the description `"3% Receipt Commission applied to Campaign"`.
