﻿CREATE TABLE [dbo].[BreadcrumbTb] (
    [BreadcrumbID]              INT                IDENTITY (1, 1) NOT FOR REPLICATION NOT NULL,
    [BreadcrumbUID]             VARCHAR (100)      NOT NULL,
    [BreadcrumbActivityUID]     VARCHAR (100)      NULL,
    [BreadcrumbProjectID]       INT                NOT NULL,
    [BreadcrumbSourceID]        VARCHAR (100)      NULL,
    [BreadcrumbCreatedUserUID]  VARCHAR (100)      NOT NULL,
    [BreadcrumbSrcDTLT]         DATETIME           NOT NULL,
    [BreadcrumbSrvDTLTOffset]   DATETIMEOFFSET (7) CONSTRAINT [DF_BreadcrumbTb_SrvDTLTOffset] DEFAULT (sysdatetimeoffset()) NULL,
    [BreadcrumbSrvDTLT]         DATETIME           CONSTRAINT [DF_BreadcrumbTb_SrvDTLT] DEFAULT (getdate()) NULL,
    [BreadcrumbGPSType]         VARCHAR (100)      NULL,
    [BreadcrumbGPSSentence]     VARCHAR (400)      NULL,
    [BreadcrumbLatitude]        FLOAT (53)         NULL,
    [BreadcrumbLongitude]       FLOAT (53)         NULL,
    [BreadcrumbShape]           [sys].[geography]  NULL,
    [BreadcrumbActivityType]    VARCHAR (50)       NULL,
    [BreadcrumbWorkQueueFilter] VARCHAR (200)      NULL,
    [BreadcrumbBatteryLevel]    FLOAT (53)         NULL,
    [BreadcrumbGPSTime]         DATETIME           NULL,
    [BreadcrumbSpeed]           FLOAT (53)         NULL,
    [BreadcrumbHeading]         VARCHAR (50)       NULL,
    [BreadcrumbGPSAccuracy]     VARCHAR (50)       NULL,
    [BreadcrumbSatellites]      INT                NULL,
    [BreadcrumbAltitude]        FLOAT (53)         NULL,
    [BreadcrumbTrackingGroupID] INT                NULL,
    [BreadcrumbMapPlat]         VARCHAR (50)       CONSTRAINT [DF_BreadcrumbTb_BreadcrumbMapPlat] DEFAULT ('') NULL,
    [BreadcrumbArchiveFlag]     BIT                CONSTRAINT [DF_BreadcrumbTb_BreadcrumbArchiveFlag] DEFAULT ((0)) NULL,
    [BreadcrumbComments]        VARCHAR (500)      NULL,
    [BreadcrumbCreatedDate]     DATETIME           CONSTRAINT [DF_BreadcrumbTb_BreadcrumbCreatedDate] DEFAULT (getdate()) NULL,
    [BreadcrumbDeviceID]        VARCHAR (50)       NULL,
    CONSTRAINT [PK_BreadcrumbsTb] PRIMARY KEY CLUSTERED ([BreadcrumbID] ASC)
);

