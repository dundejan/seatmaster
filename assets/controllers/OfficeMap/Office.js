import React from 'react';
import { useRef } from 'react';
import { useDrop } from 'react-dnd';
import PropTypes from "prop-types";

export default function Office({ onDropChair, children }) {
	const officeRef = useRef(null);

	const [, drop] = useDrop({
		accept: 'SEAT',
		drop: (item, monitor) => {
			const officeElement = officeRef.current;
			const dropCoordinates = monitor.getSourceClientOffset();

			if (officeElement) {
				const officeRect = officeElement.getBoundingClientRect();
				const relativeX = Math.round(dropCoordinates.x - officeRect.left);
				const relativeY = Math.round(dropCoordinates.y - officeRect.top);

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
		backgroundColor: '#f7f7f7'
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