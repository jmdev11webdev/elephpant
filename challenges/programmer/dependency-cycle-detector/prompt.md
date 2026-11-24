# Step 3: Dependency Cycle Detector

> **Concept Reference: Directed Graph Cycle Detection (3-Color DFS)**
> In a directed graph, a cycle exists if and only if a depth-first search (DFS) reaches a node that is currently active on the recursion stack (`visiting` / gray state). Fully explored nodes are marked `visited` (black) to guarantee `O(V + E)` runtime.

## Requirements

1. Implement `hasDependencyCycle(array $graph): bool` in `CycleDetector.php`, where `$graph` maps each package name to a list of dependency names.
2. Return `true` if the directed graph contains any cycle (including self-dependencies such as `A -> A`).
3. Return `false` if the graph is a valid Directed Acyclic Graph (DAG).
4. Handle disconnected components and leaf packages that appear only as targets.
5. **Note:** `$graph` is passed in automatically by the test runner—do not overwrite it with hardcoded graphs.

## Example

```php
hasDependencyCycle(['app' => ['auth'], 'auth' => ['crypto']]); // Returns: false
hasDependencyCycle(['a' => ['b'], 'b' => ['a']]);              // Returns: true
```
