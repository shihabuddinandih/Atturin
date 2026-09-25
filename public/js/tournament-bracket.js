/**
 * Lays out a knockout bracket as a proper tree: each round is a column,
 * each match's vertical center is the midpoint of its two feeder matches
 * from the previous round (the standard recursive bracket layout), with
 * SVG connector lines drawn between them. Every match box is the same
 * height (bye matches included — their empty side renders as a "(bye)"
 * placeholder row rather than being collapsed) so the whole tree lines up
 * uniformly.
 *
 * Expected DOM contract per tree:
 *   <div class="bracket-tree">
 *     <div class="bracket-round">
 *       <div class="bracket-match" data-bye="0|1">...content...</div>
 *       ...
 *     </div>
 *     ...
 *   </div>
 *
 * Round order and match order within a round must match tournament_rounds
 * .urutan and tournament_matches.bracket_position — i.e. round N's match m
 * is fed by round N-1's matches (2m) and (2m+1).
 */
(function () {
    const COL_WIDTH = 220;
    const COL_GAP = 56;
    const MATCH_HEIGHT = 60;
    const ROW_GAP = 16;

    function layoutTree(tree) {
        const rounds = Array.from(tree.querySelectorAll(':scope > .bracket-round'));
        if (rounds.length === 0) return;

        // Round 0: stack from the top. Every match — bye or not — is the
        // same height, so all boxes in the bracket line up uniformly.
        let centers = [];
        let cursorY = 0;
        const round0Matches = Array.from(rounds[0].querySelectorAll(':scope > .bracket-match'));
        round0Matches.forEach((el) => {
            const centerY = cursorY + MATCH_HEIGHT / 2;
            centers.push(centerY);
            el.style.position = 'absolute';
            el.style.left = '0px';
            el.style.top = cursorY + 'px';
            el.style.width = COL_WIDTH + 'px';
            cursorY += MATCH_HEIGHT + ROW_GAP;
        });
        rounds[0].style.position = 'relative';
        rounds[0].style.width = COL_WIDTH + 'px';
        rounds[0].style.flexShrink = '0';
        rounds[0].style.height = Math.max(0, cursorY - ROW_GAP) + 'px';

        const connectors = [];

        // Round 1..N: center = midpoint of the two feeders from the previous round.
        for (let r = 1; r < rounds.length; r++) {
            const matches = Array.from(rounds[r].querySelectorAll(':scope > .bracket-match'));
            const nextCenters = [];

            matches.forEach((el, m) => {
                const feederA = centers[m * 2];
                const feederB = centers[m * 2 + 1];
                const centerY = (feederA !== undefined && feederB !== undefined)
                    ? (feederA + feederB) / 2
                    : (feederA !== undefined ? feederA : 0);

                nextCenters.push(centerY);
                el.style.position = 'absolute';
                el.style.left = '0px';
                el.style.top = (centerY - MATCH_HEIGHT / 2) + 'px';
                el.style.width = COL_WIDTH + 'px';

                if (feederA !== undefined && feederB !== undefined) {
                    connectors.push({ round: r, feederA, feederB, centerY });
                }
            });

            const roundHeight = Math.max(...nextCenters.map((c) => c + MATCH_HEIGHT / 2), 0);
            rounds[r].style.position = 'relative';
            rounds[r].style.width = COL_WIDTH + 'px';
            rounds[r].style.flexShrink = '0';
            rounds[r].style.height = roundHeight + 'px';

            centers = nextCenters;
        }

        drawConnectors(tree, connectors);
    }

    function drawConnectors(tree, connectors) {
        const existing = tree.querySelector(':scope > svg.bracket-lines');
        if (existing) existing.remove();

        const treeWidth = tree.scrollWidth;
        const treeHeight = tree.scrollHeight;

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'bracket-lines');
        svg.setAttribute('width', treeWidth);
        svg.setAttribute('height', treeHeight);
        svg.style.position = 'absolute';
        svg.style.top = '0';
        svg.style.left = '0';
        svg.style.pointerEvents = 'none';
        svg.style.zIndex = '0';

        connectors.forEach(({ round, feederA, feederB, centerY }) => {
            const feederRightX = (round - 1) * (COL_WIDTH + COL_GAP) + COL_WIDTH; // right edge of the feeder round
            const currentLeftX = round * (COL_WIDTH + COL_GAP);                    // left edge of this round
            const midX = (feederRightX + currentLeftX) / 2;                        // midpoint of the gap between them

            [feederA, feederB].forEach((y) => {
                svg.appendChild(makeLine(midX, y, feederRightX, y));
            });
            svg.appendChild(makeLine(midX, feederA, midX, feederB));
            svg.appendChild(makeLine(midX, centerY, currentLeftX, centerY));
        });

        tree.style.position = 'relative';
        tree.insertBefore(svg, tree.firstChild);
    }

    function makeLine(x1, y1, x2, y2) {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', '#D1D5DB');
        line.setAttribute('stroke-width', '2');
        return line;
    }

    function init() {
        document.querySelectorAll('.bracket-tree').forEach(layoutTree);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.reinitBracketTree = init;
})();
