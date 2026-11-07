# VMI Workflow

## 1. Relationship

Buyer invites supplier (`SupplierRelationshipService::invite`); buyer approves (`approve`). Status: `pending` → `approved`.

## 2. Agreement & policy

Create **vmi_agreement** linked to the relationship. Attach **vmi_policies** per product/warehouse:

| Policy type | Behavior (ReplenishmentEngine) |
|-------------|-------------------------------|
| `min_max` | Replenish toward `max_stock` |
| `reorder_point` | Order when position ≤ ROP |
| `days_of_supply` | Target ADC × days + safety stock |

Supplier catalog: **supplier_products** (price, MOQ, multiples, lead time).

## 3. Inventory & demand

**inventory_balances** hold sellable/reserved/allocated/inbound. **demand_events** feed average daily consumption (ADC).

Inventory position = `sellable + confirmed_inbound - reserved - allocated`.

## 4. Replenishment run

`ReplenishmentEngine::runForRelationship` creates a **replenishment_run** and **replenishment_recommendations** with explanations and constraint-adjusted quantities.

## 5. Proposal → PO

`ProposalService`: create from recommendations → submit → approve → `PurchaseOrderService::generateFromProposal`.

## 6. Fulfillment

`PurchaseOrderService::issue` / `acknowledge` → `ShipmentService` dispatch → `GoodsReceiptService::receive` posts inventory and updates PO line receipt status.

## Scheduler

`vmi:replenishment-evaluate` re-runs the engine for all approved relationships daily.
