import React, { useEffect, useState, useRef } from 'react';
import { useDrop } from 'react-dnd';
import PropTypes from "prop-types";
import axios from "axios";
import {updateOfficeSize} from "../api/api";
import {Box, Button, Card, CardContent, CardHeader, TextField, Typography} from "@mui/material";

function roundToNearest50(n) {
	return Math.round(n / 50) * 50;
}

export default function Office({ onDropChair, officeId, showPopupMessage, children }) {
	const officeRef = useRef(null);
	const [size, setSize] = useState(0);
	const [width, setWidth] = useState(0);
	const [stagedHeight, setStagedHeight] = useState(0);
	const [stagedWidth, setStagedWidth] = useState(0);
	const [isSaving, setIsSaving] = useState(false);
	const [saveError, setSaveError] = useState(null);

	useEffect(() => {
		(async () => {
			await fetchOfficeData();
		})();
	}, [officeId]);

	useEffect(() => {
		setStagedHeight(size);
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

	const handleHeightChange = (e) => setStagedHeight(parseInt(e.target.value, 10));
	const handleWidthChange = (e) => setStagedWidth(parseInt(e.target.value, 10));

	const updateSizeInBackend = async () => {
		setIsSaving(true);
		setSaveError(null);

		try {
			const response = await updateOfficeSize(officeId, stagedHeight, stagedWidth);
			if (response.redirected === true) {
				// Using the function from parent
				showPopupMessage("Office size was not updated. Access denied. You need to have admin rights.");
				console.log("Seat coordinates were not updated. Access denied.");
			}
			else {
				setSize(stagedHeight);
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
		<div className="row">
			<div className="col-md-2 ps-md-3">
				<Card sx={{ maxWidth: 350, display: 'flex', flexDirection: 'column', alignItems: 'center', marginTop: '10px' }}>
					<CardHeader
						subheader="OFFICE SIZE"
						subheaderTypographyProps={{ variant: 'body2', style: { marginBottom: '-15px' } }}
					/>
					<CardContent sx={{ textAlign: 'center' }}>
						<Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '10px' }}>
						<TextField
							label="Height"
							type="number"
							value={stagedHeight}
							onChange={handleHeightChange}
							variant="outlined"
							size="small"
							style={{ width: '100px' }}
							disabled={isSaving}
						/>
						<TextField
							label="Width"
							type="number"
							value={stagedWidth}
							onChange={handleWidthChange}
							variant="outlined"
							size="small"
							style={{ width: '100px' }}
							disabled={isSaving}
						/>
						<Button
							variant="contained"
							color="primary"
							onClick={updateSizeInBackend}
							disabled={isSaving}
						>
							Change
						</Button>
					</Box>
				</CardContent>
			</Card>
				{saveError && <p style={{ color: 'red' }}>{saveError}</p>}
			</div>
			<div className="col-md-9">
				<Typography variant="caption" color="textSecondary">
					OFFICE:
				</Typography>
				<div ref={officeRef} style={dropAreaStyle}>{children}</div>
			</div>
		</div>
	);
}

Office.propTypes = {
	onDropChair: PropTypes.func.isRequired,
	showPopupMessage: PropTypes.func.isRequired,
	children: PropTypes.oneOfType([
		PropTypes.arrayOf(PropTypes.node),
		PropTypes.node
	]).isRequired,
	officeId: PropTypes.string.isRequired
};
