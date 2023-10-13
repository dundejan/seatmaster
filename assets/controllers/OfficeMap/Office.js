import React, { useEffect, useState, useRef } from 'react';
import { useDrop } from 'react-dnd';
import PropTypes from "prop-types";
import axios from "axios";
import {updateOfficeSize} from "../api/seats_api";
import PopupWarning from "../Warnings/PopupWarning";

function roundToNearest50(n) {
	return Math.round(n / 50) * 50;
}

export default function Office({ onDropChair, officeId, children }) {
	const officeRef = useRef(null);
	const [size, setSize] = useState(0);
	const [width, setWidth] = useState(0);
	const [stagedSize, setStagedSize] = useState(0);
	const [stagedWidth, setStagedWidth] = useState(0);
	const [isSaving, setIsSaving] = useState(false);
	const [saveError, setSaveError] = useState(null);
	const [showPopup, setShowPopup] = useState(false);

	const handleShowPopup = () => {
		setShowPopup(true);
	};

	const handleClosePopup = () => {
		setShowPopup(false);
	};

	useEffect(() => {
		fetchOfficeData();
	}, [officeId]);

	useEffect(() => {
		setStagedSize(size);
		setStagedWidth(width);
	}, [size, width]);

	const fetchOfficeData = async () => {
		try {
			const { data } = await axios.get(`/api/offices/${officeId}`);
			setSize(data.height);
			setWidth(data.width);
		} catch (error) {
			console.error('Failed to fetch office data:', error);
		}
	};

	const handleSizeChange = (e) => setStagedSize(parseInt(e.target.value, 10));
	const handleWidthChange = (e) => setStagedWidth(parseInt(e.target.value, 10));

	const updateSizeInBackend = async () => {
		setIsSaving(true);
		setSaveError(null);

		try {
			const response = await updateOfficeSize(officeId, stagedSize, stagedWidth);
			if (response.redirected === true) {
				handleShowPopup();
				console.log("Seat coordinates were not updated. Access denied.");
			}
			else {
				setSize(stagedSize);
				setWidth(stagedWidth);
			}
		} catch (error) {
			setSaveError(error.message);
		} finally {
			setIsSaving(false);
		}
	};

	const [, drop] = useDrop({
		accept: 'SEAT',
		drop: (item, monitor) => {
			const dropCoordinates = monitor.getSourceClientOffset();
			const officeRect = officeRef.current.getBoundingClientRect();
			const relativeX = roundToNearest50(dropCoordinates.x - officeRect.left);
			const relativeY = roundToNearest50(dropCoordinates.y - officeRect.top);
			onDropChair(item.id, { x: relativeX, y: relativeY });
		}
	});

	drop(officeRef);

	const dropAreaStyle = {
		position: 'relative',
		width: `${width}px`,
		height: `${size}px`,
		border: '2px dashed #666',
		backgroundColor: '#f7f7f7',
		backgroundImage: `linear-gradient(0deg, transparent 49%, rgba(102, 102, 102, 0.4) 49%, rgba(102, 102, 102, 0.4) 51%, transparent 51%),
                          linear-gradient(90deg, transparent 49%, rgba(102, 102, 102, 0.4) 49%, rgba(102, 102, 102, 0.4) 51%, transparent 51%)`,
		backgroundSize: '50px 50px'
	};

	return (
		<div>
			<div style={{ marginBottom: '10px' }}>
				<label>
					Size:
					<input type="number" value={stagedSize} onChange={handleSizeChange} style={{ width: '60px', marginLeft: '5px' }} disabled={isSaving} />
				</label>
				<label style={{ marginLeft: '10px' }}>
					Width:
					<input type="number" value={stagedWidth} onChange={handleWidthChange} style={{ width: '60px', marginLeft: '5px' }} disabled={isSaving} />
				</label>
				<button onClick={updateSizeInBackend} disabled={isSaving} style={{ marginLeft: '10px' }}>Change</button>
			</div>
			{saveError && <p style={{ color: 'red' }}>{saveError}</p>}
			Office
			<div ref={officeRef} style={dropAreaStyle}>{children}</div>
			<div>
				{showPopup && (
					<PopupWarning message="Office size was not updated. Access denied. You need to have admin rights. " onClose={handleClosePopup} />
				)}
			</div>
		</div>
	);
}

Office.propTypes = {
	onDropChair: PropTypes.func.isRequired,
	children: PropTypes.oneOfType([
		PropTypes.arrayOf(PropTypes.node),
		PropTypes.node
	]).isRequired,
	officeId: PropTypes.string.isRequired
};
