import React from 'react';
import { useRef } from 'react';
import { useDrop } from 'react-dnd';
import PropTypes from "prop-types";

function roundToNearest50(n) {
	return Math.round(n / 50) * 50;
}

export default function Office({ onDropChair, children }) {
	const officeRef = useRef(null);

	const [, drop] = useDrop({
		accept: 'SEAT',
		drop: (item, monitor) => {
			const officeElement = officeRef.current;
			const dropCoordinates = monitor.getSourceClientOffset();

			if (officeElement) {
				const officeRect = officeElement.getBoundingClientRect();
				const relativeX = roundToNearest50(dropCoordinates.x - officeRect.left);
				const relativeY = roundToNearest50(dropCoordinates.y - officeRect.top);

				onDropChair(item.id, { x: relativeX, y: relativeY });
			}
		}
	});

	drop(officeRef);  // Attach the useDrop ref to the officeRef.

	const dropAreaStyle = {
		position: 'relative',
		width: '100%',
		height: '400px',
		border: '2px dashed #666',
		backgroundColor: '#f7f7f7',
		backgroundImage: `
        linear-gradient(0deg, transparent 49%, rgba(102, 102, 102, 0.4) 49%, rgba(102, 102, 102, 0.4) 51%, transparent 51%),
        linear-gradient(90deg, transparent 49%, rgba(102, 102, 102, 0.4) 49%, rgba(102, 102, 102, 0.4) 51%, transparent 51%)
    `,
		backgroundSize: '50px 50px'
	};

	return (
		<div ref={officeRef} style={dropAreaStyle}>
			Drop chairs here
			{children}
		</div>
	);
}

Office.propTypes = {
	onDropChair: PropTypes.func.isRequired,
	children: PropTypes.oneOfType([
		PropTypes.arrayOf(PropTypes.node),
		PropTypes.node
	]).isRequired,
}