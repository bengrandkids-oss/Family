// app/Services/FamilyInferenceEngine.php
<?php

namespace App\Services;

use App\Models\Member;
use App\Models\FamilyRelation;
use Illuminate\Support\Collection;

class FamilyInferenceEngine
{
    /** @var array<int, Collection> Cached facts per family cluster */
    private array $clusters = [];

    /** @var array<int, int> Maps member_id => cluster_index */
    private array $memberClusterMap = [];

    private bool $built = false;

    // -----------------------------------------------------------------------
    // Cluster builder — runs once, lazily
    // -----------------------------------------------------------------------

    private function buildClusters(): void
    {
        if ($this->built) return;

        $allMemberIds = Member::pluck('id')->all();
        $visited      = [];
        $clusterIndex = 0;

        foreach ($allMemberIds as $memberId) {
            if (isset($visited[$memberId])) continue;

            // BFS: expand outward through any spouse or child link
            $clusterMembers = $this->expandCluster($memberId, $visited);

            // Load only the facts relevant to this cluster
            $ids = array_keys($clusterMembers);
            $facts = FamilyRelation::whereIn('member_id', $ids)
                ->whereIn('related_member_id', $ids)
                ->get();

            $this->clusters[$clusterIndex] = $facts;

            foreach ($ids as $id) {
                $this->memberClusterMap[$id] = $clusterIndex;
                $visited[$id] = true;
            }

            $clusterIndex++;
        }

        $this->built = true;
    }

    /**
     * BFS traversal to find all members connected to a starting member.
     * Uses chunked DB queries per frontier to avoid N+1.
     *
     * @return array<int, true>
     */
    private function expandCluster(int $startId, array $visited): array
    {
        $cluster  = [];
        $frontier = [$startId];

        while (!empty($frontier)) {
            $newFrontier = [];

            // Fetch all relations where any frontier member appears on either side
            $relations = FamilyRelation::whereIn('member_id', $frontier)
                ->orWhereIn('related_member_id', $frontier)
                ->get();

            foreach ($frontier as $id) {
                $cluster[$id] = true;
                $visited[$id] = true;
            }

            foreach ($relations as $relation) {
                foreach ([$relation->member_id, $relation->related_member_id] as $id) {
                    if (!isset($cluster[$id]) && !isset($visited[$id])) {
                        $newFrontier[] = $id;
                        $visited[$id]  = true; // mark early to avoid duplicate frontier entries
                    }
                }
            }

            $frontier = array_unique($newFrontier);
        }

        return $cluster;
    }

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    public function infer(int $fromId, int $toId): string
    {
        $this->buildClusters();

        // Members in different clusters have no relation
        if (($this->memberClusterMap[$fromId] ?? -1) !== ($this->memberClusterMap[$toId] ?? -2)) {
            return 'no relation found';
        }

        $facts = $this->clusters[$this->memberClusterMap[$fromId]];

        $rules = $this->getRules($facts);

        foreach ($rules as $relation => $check) {
            if ($check($fromId, $toId)) {
                return $relation;
            }
        }

        return 'no relation found';
    }

    public function inferAll(int $memberId): array
    {
        $this->buildClusters();

        $clusterIndex = $this->memberClusterMap[$memberId] ?? null;
        if ($clusterIndex === null) return [];

        // Only iterate members within the same cluster
        $clusterMemberIds = array_keys(
            array_filter($this->memberClusterMap, fn($c) => $c === $clusterIndex)
        );

        $results = [];
        foreach ($clusterMemberIds as $otherId) {
            if ($otherId === $memberId) continue;
            $relation = $this->infer($memberId, $otherId);
            if ($relation !== 'no relation found') {
                $results[$otherId] = $relation;
            }
        }

        return $results;
    }

    // -----------------------------------------------------------------------
    // Rules (same logic, but now scoped to a cluster's facts)
    // -----------------------------------------------------------------------

    private function getRules(Collection $facts): array
    {
        return [
            'spouse'         => fn($a, $b) => $this->isSpouse($a, $b, $facts),
            'child'          => fn($a, $b) => $this->isChild($a, $b, $facts),
            'parent'         => fn($a, $b) => $this->isParent($a, $b, $facts),
            'sibling'        => fn($a, $b) => $this->isSibling($a, $b, $facts),
            'grandparent'    => fn($a, $b) => $this->isGrandparent($a, $b, $facts),
            'grandchild'     => fn($a, $b) => $this->isGrandchild($a, $b, $facts),
            'parent-in-law'  => fn($a, $b) => $this->isParentInLaw($a, $b, $facts),
            'child-in-law'   => fn($a, $b) => $this->isChildInLaw($a, $b, $facts),
            'sibling-in-law' => fn($a, $b) => $this->isSiblingInLaw($a, $b, $facts),
            'aunt/uncle'     => fn($a, $b) => $this->isAuntOrUncle($a, $b, $facts),
            'niece/nephew'   => fn($a, $b) => $this->isNieceOrNephew($a, $b, $facts),
            'cousin'         => fn($a, $b) => $this->isCousin($a, $b, $facts),
        ];
    }

    // -----------------------------------------------------------------------
    // Fact lookups (now accept scoped $facts)
    // -----------------------------------------------------------------------

    private function isSpouse(int $a, int $b, Collection $facts): bool
    {
        return $facts->contains(fn($f) =>
            ($f->member_id === $a && $f->related_member_id === $b && $f->relation_type === 'spouse') ||
            ($f->member_id === $b && $f->related_member_id === $a && $f->relation_type === 'spouse')
        );
    }

    private function isChild(int $a, int $b, Collection $facts): bool
    {
        return $facts->contains(fn($f) =>
            $f->member_id === $b &&
            $f->related_member_id === $a &&
            $f->relation_type === 'child'
        );
    }

    private function isParent(int $a, int $b, Collection $facts): bool
    {
        return $facts->contains(fn($f) =>
            $f->member_id === $a &&
            $f->related_member_id === $b &&
            $f->relation_type === 'child'
        );
    }

    private function childrenOf(int $id, Collection $facts): Collection
    {
        return $facts->where('member_id', $id)->where('relation_type', 'child')->pluck('related_member_id');
    }

    private function parentsOf(int $id, Collection $facts): Collection
    {
        return $facts->where('related_member_id', $id)->where('relation_type', 'child')->pluck('member_id');
    }

    private function spousesOf(int $id, Collection $facts): Collection
    {
        $asA = $facts->where('member_id', $id)->where('relation_type', 'spouse')->pluck('related_member_id');
        $asB = $facts->where('related_member_id', $id)->where('relation_type', 'spouse')->pluck('member_id');
        return $asA->merge($asB)->unique();
    }

    // -----------------------------------------------------------------------
    // Derived rules
    // -----------------------------------------------------------------------

    private function isSibling(int $a, int $b, Collection $facts): bool
    {
        if ($a === $b) return false;
        return $this->parentsOf($a, $facts)->intersect($this->parentsOf($b, $facts))->isNotEmpty();
    }

    private function isGrandparent(int $a, int $b, Collection $facts): bool
    {
        return $this->parentsOf($b, $facts)->contains(fn($p) => $this->isParent($a, $p, $facts));
    }

    private function isGrandchild(int $a, int $b, Collection $facts): bool
    {
        return $this->isGrandparent($b, $a, $facts);
    }

    private function isParentInLaw(int $a, int $b, Collection $facts): bool
    {
        return $this->spousesOf($b, $facts)->contains(fn($s) => $this->isParent($a, $s, $facts));
    }

    private function isChildInLaw(int $a, int $b, Collection $facts): bool
    {
        return $this->isParentInLaw($b, $a, $facts);
    }

    private function isSiblingInLaw(int $a, int $b, Collection $facts): bool
    {
        $spouseSibling = $this->spousesOf($b, $facts)->contains(fn($s) => $this->isSibling($a, $s, $facts));
        $siblingSpouse = $this->spousesOf($a, $facts)->contains(fn($s) => $this->isSibling($s, $b, $facts));
        return $spouseSibling || $siblingSpouse;
    }

    private function isAuntOrUncle(int $a, int $b, Collection $facts): bool
    {
        return $this->parentsOf($b, $facts)->contains(fn($p) => $this->isSibling($a, $p, $facts));
    }

    private function isNieceOrNephew(int $a, int $b, Collection $facts): bool
    {
        return $this->isAuntOrUncle($b, $a, $facts);
    }

    private function isCousin(int $a, int $b, Collection $facts): bool
    {
        foreach ($this->parentsOf($a, $facts) as $pa) {
            foreach ($this->parentsOf($b, $facts) as $pb) {
                if ($this->isSibling($pa, $pb, $facts)) return true;
            }
        }
        return false;
    }
}
