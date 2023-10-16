import React from "react";
import { createRoot } from 'react-dom/client';
import OfficeMapApp from './OfficeMap/OfficeMapApp';

const targetElement = document.getElementById('office-map');
const officeId = targetElement.getAttribute('data-office-id');
const officeName = targetElement.getAttribute('data-office-name');

const root = createRoot(targetElement);

root.render(
	<OfficeMapApp officeId={officeId} officeName={officeName}/>
)