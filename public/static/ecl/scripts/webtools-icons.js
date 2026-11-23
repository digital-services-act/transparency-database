(function () {
    'use strict';

    var iconSelector = '[data-wt-icon]';
    var svgNamespace = 'http://www.w3.org/2000/svg';

    function cloneSvgNode(node) {
        if (node.nodeType === 1) {
            var clone = document.createElementNS(svgNamespace, node.localName);

            Array.prototype.forEach.call(node.attributes, function (attribute) {
                if (attribute.namespaceURI) {
                    clone.setAttributeNS(attribute.namespaceURI, attribute.name, attribute.value);
                } else {
                    clone.setAttribute(attribute.name, attribute.value);
                }
            });

            Array.prototype.forEach.call(node.childNodes, function (child) {
                var childClone = cloneSvgNode(child);

                if (childClone) {
                    clone.appendChild(childClone);
                }
            });

            return clone;
        }

        if (node.nodeType === 3) {
            return document.createTextNode(node.nodeValue);
        }

        if (node.nodeType === 8) {
            return document.createComment(node.nodeValue);
        }

        return null;
    }

    function getIconSvg(iconName) {
        var cssValue = window.getComputedStyle(document.documentElement)
            .getPropertyValue('--wt-icon--' + iconName)
            .trim();

        if (!cssValue) {
            return null;
        }

        var parsed = new DOMParser().parseFromString(cssValue, 'image/svg+xml');
        var svg = parsed.documentElement;

        if (!svg || svg.localName !== 'svg' || parsed.querySelector('parsererror')) {
            return null;
        }

        return {
            nodes: Array.prototype.map.call(svg.childNodes, function (node) {
                return cloneSvgNode(node);
            }).filter(Boolean),
            viewBox: svg.getAttribute('viewBox')
        };
    }

    function hydrate(root) {
        if (!root || typeof document === 'undefined') {
            return;
        }

        var markers = [];

        if (root.nodeType === 1 && root.matches(iconSelector)) {
            markers.push(root);
        }

        markers = markers.concat(Array.prototype.slice.call(root.querySelectorAll(iconSelector)));

        markers.forEach(function (marker) {
            var iconName = marker.getAttribute('data-wt-icon');
            var parentSvg = marker.closest('svg');

            if (!iconName || !parentSvg || marker.hasAttribute('data-wt-icon-hydrated')) {
                return;
            }

            var iconSvg = getIconSvg(iconName);

            if (!iconSvg) {
                return;
            }

            if (iconSvg.viewBox) {
                parentSvg.setAttribute('viewBox', iconSvg.viewBox);
            } else if (!parentSvg.hasAttribute('viewBox')) {
                parentSvg.setAttribute('viewBox', '0 0 48 48');
            }

            iconSvg.nodes.forEach(function (node) {
                parentSvg.insertBefore(node, marker);
            });

            marker.setAttribute('data-wt-icon-hydrated', '');
            marker.remove();
        });
    }

    window.EclLocalWebToolsIcons = window.EclLocalWebToolsIcons || {};
    window.EclLocalWebToolsIcons.hydrate = hydrate;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            hydrate(document);
        });
    } else {
        hydrate(document);
    }
}());
