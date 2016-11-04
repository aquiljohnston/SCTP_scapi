CREATE TABLE [dbo].[xOQEquipmentTypexRef] (
    [OQEquipmentxRefID]     INT           IDENTITY (1, 1) NOT NULL,
    [OQProfile]             VARCHAR (10)  NULL,
    [SAPEquipmentType]      VARCHAR (50)  NULL,
    [EquipmentDisplayType]  VARCHAR (50)  NULL,
    [RequiredForLoginFlag]  BIT           CONSTRAINT [DF_OQ_EquipmentxRefID_RequiredForLoginFlag] DEFAULT ((0)) NULL,
    [RequiredForLoginAndOr] VARCHAR (10)  NULL,
    [Revision]              INT           CONSTRAINT [DF_OQ_EquipmentxRefID_Revision] DEFAULT ((0)) NULL,
    [ActiveFlag]            BIT           CONSTRAINT [DF_OQ_EquipmentxRefID_ActiveFlag] DEFAULT ((1)) NULL,
    [Comments]              VARCHAR (500) NULL,
    [RequirementType1]      VARCHAR (10)  NULL,
    [RequirementType2]      VARCHAR (10)  NULL,
    [FootFlag]              BIT           NULL,
    [MobleFlag]             BIT           NULL,
    [FoundByFlag]           BIT           NULL,
    [GradeByFlag]           BIT           NULL,
    [EZTechEquipmentType]   VARCHAR (200) NULL,
    [WebDisplayType]        VARCHAR (25)  CONSTRAINT [DF_xOQEquipmentTypexRef_WebDisplayType] DEFAULT ('') NULL,
    [INFDisplayType]        VARCHAR (25)  CONSTRAINT [DF_xOQEquipmentTypexRef_INFDisplayType] DEFAULT ('') NULL
);



