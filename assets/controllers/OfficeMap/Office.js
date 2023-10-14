import React, { useEffect, useState, useRef } from 'react';
import { useDrop } from 'react-dnd';
import PropTypes from "prop-types";
import axios from "axios";
import {addSeat, updateOfficeSize} from "../api/api";
import {Box, Button, Card, CardContent, CardHeader, TextField, Typography} from "@mui/material";
import {Seat} from "./Seat";

function roundToNearest50(n) {
	return Math.round(n / 50) * 50;
}

export default function Office({ onDropChair, officeId, officeName, showPopupMessage, chairs, addNewChair }) {
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

	const addSeatInBackend = async () => {
		setIsSaving(true);
		setSaveError(null);

		try {
			const response = await addSeat(officeId);
			if (response.redirected === true) {
				// Using the function from parent
				showPopupMessage("Seat was not added. Access denied. You need to have admin rights.");
			}
			else {
				const newSeat = await response.json();
				console.log(newSeat);
				addNewChair(newSeat);
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
		<div className="row mt-2">
			<div className="col-md-3 ps-md-4">
				<Card sx={{ maxWidth: 350, display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
					<CardHeader
						title="EASY ADMIN"
						subheader="OFFICE SIZE"
						subheaderTypographyProps={{ variant: 'body1', style: { marginBottom: '-15px', textAlign: 'center' } }}
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
							<i className="fa-solid fa-maximize"></i>&nbsp;Change
						</Button>
					</Box>
						<CardHeader
							subheader="OFFICE SEATS"
							subheaderTypographyProps={{ variant: 'body1', style: { marginBottom: '-15px', textAlign: 'center' } }}
						/>
						<CardContent sx={{ textAlign: 'center' }}>
							<Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '10px' }}>
								<Button
									variant="contained"
									color="primary"
									onClick={addSeatInBackend}
									disabled={isSaving}
								>
									<i className="fa-solid fa-square-plus"></i>&nbsp;Add new
								</Button>
							</Box>
						</CardContent>
				</CardContent>
			</Card>
				{saveError && <p style={{ color: 'red' }}>{saveError}</p>}
			</div>
			<div className="col-md-9">
				<div style={{ backgroundColor: '#f5f5f5', display: 'inline-block' }}>
					<Typography variant="caption" color="textSecondary" style={{ fontSize: '0.8em', textTransform: "uppercase" }}>
						&nbsp;{officeName}&nbsp;
					</Typography>
				</div>
				<div ref={officeRef} style={dropAreaStyle}>
					{chairs.map(chair => (
						<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} currentAssignments={chair.currentAssignments} />
					))}
				</div>
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
	officeId: PropTypes.string.isRequired,
	officeName: PropTypes.string.isRequired,
};
